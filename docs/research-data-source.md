# Research: data source for the moderator view

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

## Still to check

- The logged-in **Waiting** tab: what it lists that the public listing doesn't (for a PTE, probably only the projects they can moderate), and whether it has a cheaper way to get the breakdown. Nilo will provide an HTML dump.
- Whether the listing's Waiting/Fuzzy total includes changes-requested strings.
- Rate limits on translate.wordpress.org for a crawl of about 700 requests.
