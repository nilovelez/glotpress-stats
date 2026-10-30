# Handoff: the moderator extension

Everything needed to start the new project: a Chrome extension for translation editors on translate.wordpress.org. Copy this file into the new repo as its starting brief. Background and research live in this repo: `docs/moderator-view.md` and `docs/research-data-source.md`.

## The plan in three parts

1. **GlotPress Stats (this repo): translators only.** The WordPress plugin keeps its translator views (`[glotstats]`, `top` and `tasks`) and stays in maintenance. No moderator features will be added to it.
2. **A new Chrome extension, Nilo's own project (the one this handoff is for).** It adds the missing subproject links to the moderator's pages on translate.wordpress.org, and exports them.
3. **Optional: contribute it to GlotDict.** Once the extension works, port its features to [GlotDict](https://github.com/Mte90/GlotDict), the extension most moderators use, through a fork and pull request.

Part 3 is optional and comes later, but design part 2 so the port is easy (see "Keep the GlotDict port easy").

## Who it is for

Translation editors of a locale: GTEs (the whole locale) and PTEs (specific projects). They review two kinds of strings:

- **Waiting**: translations suggested by someone, pending review.
- **Fuzzy**: strings marked as doubtful, usually because a small change in the plugin's code changed the original.

*Changes requested* strings are not reviewed by moderators and are out of scope.

## How translate.wordpress.org is organized

- **Themes** have a single set of strings: `/projects/wp-themes/<slug>/<locale>/default/`.
- **Plugins** usually have four subprojects: `/projects/wp-plugins/<slug>/<subproject>/<locale>/default/`, with `stable` (released version), `dev` (trunk), `stable-readme` and `dev-readme`.
- Logged-in moderators get a **Waiting** tab in the locale page that lists the projects with waiting or fuzzy strings they can approve. A PTE only sees their own projects.

The problem: the Waiting tab lists *projects*, not subprojects. To reach the strings, the moderator opens the project, then the subproject that has them, then filters by state. Two extra clicks per project, for every project.

## What the extension does

### 1. Direct links (first priority)

On the Waiting tab, and on the public locale listing filtered by waiting and fuzzy strings (`/locale/<locale>/default/<type>/?filter=strings-waiting-and-fuzzy&page=<n>`):

- For each project card on the current page (24 per page), fetch the project's page in the locale, `/locale/<locale>/default/<type>/<slug>/`, in the background.
- From its `table.locale-sub-projects` table (one row per subproject, with columns Translated, Fuzzy, Untranslated, Waiting and Changes requested), take the subprojects with waiting or fuzzy strings, their counts and the links of those counts. Those links already point to the subproject filtered by that state, for example `/projects/wp-plugins/newsletter/stable/es/default/?filters[translated]=yes&filters[status]=waiting`.
- Add those links to the project card, so the moderator goes straight to the strings.

### 2. Export the URLs (second priority)

A button on the same page that exports the subproject URLs of the current page (about 40 for 24 projects), so the moderator can feed them to a local translation tool, then review and upload the translations. Human in the loop, not full automation.

### Rules

- **Only the page being viewed.** Never walk the whole list (600+ projects for `es`). The load is the same as opening each card by hand: 24 requests per page, sent one or a few at a time, not all at once. WordPress.org rate-limits and may answer with a CAPTCHA.
- **The moderator's own session.** The content script runs on translate.wordpress.org, so its requests are same-origin and carry the moderator's cookies and permissions. The public pages and API have no CORS headers, so this is the only way to read them from the browser.
- **The page stays as it is.** Keep the moderator's sort, filters and pagination; only add to the cards.
- **Nothing to store.** No cache, no backend, no account.

## Technical notes

- Manifest V3, a content script matched to `https://translate.wordpress.org/locale/*`. No background service worker should be needed.
- Parse the fetched pages with `DOMParser`.
- Project cards carry the project as a CSS class, `project-wp-plugins-<slug>` (and `project-wp-themes-<slug>`), as seen on the public listing.
- The GlotPress API also has public data that may be useful later: `/api/projects/<type>/<slug>/<subproject>/<locale>/default/?filters[status]=waiting` lists the waiting strings with author and dates (for prioritizing by age), and `.../export-translations/?format=po&filters[status]=waiting` exports them as a PO file. Don't call `/api/languages/<locale>/default`: it takes about 30 seconds and returns nothing.

## To confirm first

These came from the public pages only; check them before writing code:

- **The Waiting tab's URL and markup.** It was never seen logged in. Nilo has to save its HTML (or share a screenshot) so the selectors are right. The server code builds it with the same query as the public listing (`get_paged_active_sub_projects()` in `wporg-gp-routes/inc/routes/class-locale.php` in the Meta repository), so the cards are probably the same.
- **GlotDict's code.** How it is built (plain JavaScript or a build step, Manifest version, how it injects into the locale pages), so part 2 follows the same patterns.
- **Name and repo** of the new project.

## Keep the GlotDict port easy

- Plain JavaScript, no framework and no build step unless GlotDict uses one.
- One self-contained module for the feature, with the page detection, the fetching and the rendering separated, so it can be dropped into GlotDict.
- Match GlotDict's coding style where it doesn't hurt.

## Open questions

- Export format: base subproject URLs or URLs filtered by state; plain text, CSV or JSON; copy to clipboard or download a file. It depends on the local tool.
- Prioritization beyond the listing's own sorts (most or least waiting + fuzzy, newest or oldest).
