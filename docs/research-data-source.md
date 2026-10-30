# Research: data source for the moderator view

> **Outcome (2026-10-30):** the moderator view moves to a separate project, a browser extension that works on the moderator's own Waiting tab. See `docs/moderator-view.md`. The plugin-side approach proposed below was not chosen.

Milestone 1. Where to get, for a locale, the subprojects with strings pending review, their counts and direct links. Findings from probing translate.wordpress.org on 2026-10-30, without logging in, using the `es` locale.

## Summary

Everything the moderator view needs is **public**: no login is required to know which subprojects of a locale have strings waiting for review, how many, and to list the strings themselves. The problem is not access but **volume**: there is no single public request that returns the per-subproject breakdown for a whole locale. Building it takes one request per project with pending strings, about 700 for `es`.

## Public sources

### 1. Locale listing filtered by waiting + fuzzy (HTML)

`/locale/<locale>/default/<type>/?filter=strings-waiting-and-fuzzy&page=<n>`

- `<type>`: `wp-plugins`, `wp-themes`, `meta`, `patterns`, `apps`. Core (`wp`) is organized by version and has no such listing.
- Sorted by waiting + fuzzy strings, most first, 24 projects per page. Other sort options: `strings-waiting-and-fuzzy-asc`, `strings-waiting-and-fuzzy-by-modified-date` (newest first) and `-by-modified-date-asc` (oldest first), plus remaining strings and percent completed.
- For `wp-plugins` and `wp-themes` the list **ends with the last project that has pending strings**: projects with 0 are not listed (page 25 of the `es` plugins list is empty). For `patterns` it does not: it lists projects with 0 as well.
- Each project card gives: slug (CSS class `project-wp-plugins-<slug>`), name, icon, number of subprojects, **Waiting/Fuzzy total for the whole project**, remaining strings and progress.
- Size for `es`: `wp-plugins` 24 pages (about 570 projects), `wp-themes` 5 pages (about 110), `meta` 2 projects.
- It does **not** break the total down by subproject.

### 2. Project page in a locale (HTML)

`/locale/<locale>/default/<type>/<slug>/`

- A table `table.locale-sub-projects` with **one row per subproject** (four for a plugin, one for a theme) and the columns Translated, Fuzzy, Untranslated, **Waiting** and **Changes requested**.
- Every count links to the subproject already filtered by that state, which are exactly the direct links the view needs, for example `/projects/wp-plugins/newsletter/stable/es/default/?filters[translated]=yes&filters[status]=waiting`.
- About 130 KB and about 1 second per page.

### 3. GlotPress API (JSON)

- `/api/projects/<type>/<slug>/`: the project and its subprojects (`slug`, `path`, `name`), no counts.
- `/api/projects/<type>/<slug>/<subproject>/`: one translation set **per locale** (about 200), each with `current_count`, `untranslated_count`, `waiting_count`, `fuzzy_count`, `warnings_count`, `percent_translated` and `last_modified`. About 55 KB, because it includes every locale. No changes-requested count.
- `/api/projects/<type>/<slug>/<subproject>/<locale>/default/?filters[status]=waiting`: **the strings themselves**, public: original, proposed translation, author (`user.user_login`), `date_added`, `date_modified`, warnings. Useful for prioritizing by age and for automation.
- `/api/projects/wp-plugins/`: every plugin project (36 MB, no counts). Not useful.
- `/projects/<type>/<slug>/<subproject>/<locale>/default/export-translations/?format=po&filters[status]=waiting`: the waiting strings as a PO file, public. Useful for automation.

### Not available

- No CORS headers on the API or the pages, so the browser cannot call translate.wordpress.org directly: requests must go through the WordPress server.
- No public endpoint that returns per-subproject counts for a whole locale at once.

## Cost of a full locale

For `es`: 29 listing pages plus about 680 project pages, at about 1 second each, is roughly 12 minutes sequentially. That is far too slow to build on a page view, and too many requests to repeat often. Possible approaches, to decide:

- **Background crawl with storage.** WP-Cron walks the listing and then the project pages in small batches, stores the result, and the view reads it. The data is as fresh as the last crawl. It contradicts the current "no cache" decision for the translator views, but that decision was about a single cheap request.
- **Incremental refresh.** Use the listing totals (cheap: 29 requests) to detect which projects changed since the last crawl and only re-fetch those project pages.
- **Two levels in the view.** Show the project-level list from the listing (one row per project, with its Waiting/Fuzzy total), and fetch a project's subproject breakdown on demand, through the server, when the moderator expands it.

Crawling hundreds of pages is ruled out: WordPress.org rate-limits requests and may answer with a CAPTCHA at any time, and it is not a civilized use of their servers.

## Looking for a bulk source

Searched the source code of translate.wordpress.org (the Meta repository, mirrored at [WordPress/wordpress.org](https://github.com/WordPress/wordpress.org), plugins `wporg-gp-routes` and `wporg-gp-customizations`), GlotPress core, its REST API and the web.

### The logged-in Waiting tab

`Routes\Locale::get_paged_active_sub_projects()` in `wporg-gp-routes/inc/routes/class-locale.php` builds both the public listing and the Waiting tab. The Waiting tab is the same query as the public `strings-waiting-and-fuzzy` listing, over the same table (`project_translation_status`, one row of counts per project and locale, with a `has_pending` flag), with two differences:

- It only lists the projects the user can approve: all of them for a GTE or locale manager, only theirs for a PTE.
- It accepts `without-editors=1` to hide projects that have a PTE assigned.

It has the **same granularity**: one row per project, never per subproject. So it gives nothing the public listing doesn't, and an HTML dump of it is no longer needed.

The per-subproject breakdown is only built in `get_locale_project()` (the project page in a locale), one project at a time.

### Other places checked

- **WordPress REST API** (`/wp-json/`): the only translation namespace is `translate/v1`, with a single `jobs` route for internal cron jobs. The Abilities API (`wp-abilities/v1`) needs authentication, and the Meta source only registers abilities for the plugin directory, none for translations.
- **GlotPress API routes**: translate.wordpress.org removes `/api/languages` and `/api/languages/<locale>`. `/api/languages/<locale>/default` still answers, but took 31 seconds to return an empty list (and a 500 with a trailing slash). Not usable, and not to be called again.
- **The web**: no documented endpoint. The Polyglots handbook only describes the web interface. Existing tools work project by project: [Kabook PTE Project Monitor](https://wordpress.org/plugins/kabook-pte-project-monitor/) calls `/api/projects/wp-plugins/<slug>/<subproject>/` for each plugin of an author, from WP-Cron.

## Conclusion

There is no public endpoint, documented or not, that returns per-subproject counts for a whole locale. The server computes it, but only exposes it as the project-level listing (24 per page) and the per-project page.

## Proposed approach

A civilized two-level view, close to what a moderator does by hand but without the clicks:

1. **Project list from the listing.** Fetch only the listing page being viewed (`?filter=strings-waiting-and-fuzzy&page=<n>`, and the sort the moderator picks): 1 request per 24 projects. Each row is a project with its Waiting/Fuzzy total.
2. **Subprojects on demand.** When the moderator expands a project, the server fetches that project's page (`/locale/<locale>/default/<type>/<slug>/`) and returns its subprojects with the direct filtered links: 1 request per expanded project, triggered by a person.
3. **Short-lived cache** (a few minutes) for both, so repeated views and several moderators don't repeat requests. The translator views keep fetching fresh data.

That is about the same number of requests the moderator's own browser makes today, so it stays well within reasonable use.

For automation (second priority), the same two requests apply, plus the public API for the strings themselves (`/api/projects/.../<locale>/default/?filters[status]=waiting`) and the PO export.

**Last resort: a feature request to the Meta team** for an API route with the locale listing and its per-subproject counts. `get_paged_active_sub_projects()` already has the query; it would only need a JSON route with the subproject breakdown.

Changes-requested strings are out of scope: moderators review waiting and fuzzy strings only.
