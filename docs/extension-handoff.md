# Moderator links in GlotDict

The moderator features (direct subproject links on the Waiting tab, and exporting them) are built as a feature of [GlotDict](https://github.com/Mte90/GlotDict), in Nilo's fork. All the docs for this work live here, in the GlotPress Stats repo, so the GlotDict repo only gets the feature's code and the pull request stays clean. Background and research: `docs/moderator-view.md` and `docs/research-data-source.md`.

## The plan

1. **GlotPress Stats (this repo): translators only.** The WordPress plugin keeps its translator views (`[glotstats]`, `top` and `tasks`) and stays in maintenance. No moderator features will be added to it.
2. **Moderator links, a feature of GlotDict.** Built on a feature branch of Nilo's fork, `nilovelez/GlotDict`, as an addition that touches existing code as little as possible. Nilo uses that branch as an unpacked extension until it is stable and tested; then, when he asks, it goes to the original repo, `Mte90/GlotDict`, as a pull request.
3. **Longer term: move GlotDict features into GlotPress core.** Contribute to GlotDict and work with the GlotPress team so that features that belong in GlotPress itself (for example, visual approve and reject buttons) go into GlotPress core and can then be removed from GlotDict.

## Working with the fork

| | |
|---|---|
| Fork | https://github.com/nilovelez/GlotDict (`origin`) |
| Original | https://github.com/Mte90/GlotDict (`upstream`) |
| Clone on Bender | `~/repos/glotdict` |
| Feature branch | `waiting-subproject-links`, created from `master` at `9a50f30` (GlotDict 2.1.6) |

- The fork's `master` stays identical to `upstream/master`; never commit to it.
- All the work goes to the feature branch: commit and push as you go, so Nilo can pull it on the computers where he uses the extension. It is the only feature branch in this setup, because it will become a pull request.
- Bring in upstream changes from time to time (`git fetch upstream`, update `master`, then merge or rebase the feature branch), so the pull request has no conflicts.
- The pull request to `Mte90/GlotDict` is opened only when Nilo asks.
- Commit messages follow GlotDict's history, not this repo's style.

### Using the branch on another computer

```bash
git clone https://github.com/nilovelez/GlotDict.git glotdict
cd glotdict
git switch waiting-subproject-links
git pull   # to get new changes, then reload the extension
```

Keep that clone on the feature branch: the browser loads whatever is checked out. See "Manifest V2 and Chrome" for how to load it.

## How GlotDict is built

Findings from reading the code at 2.1.6:

- **No build step.** Plain JavaScript with jQuery (from the translate.wordpress.org page), global `gd_*` functions, ESLint config in `.eslintrc` (globals are declared there).
- **Loading.** `manifest.json` has a single content script, `js/init.js`, on `*://translate.wordpress.org/*`. `init.js` injects the rest of the files into the page as `<script>` tags, in the order of its `jsScripts` array, so they run in the page's own context (with access to `$gp` and the page's jQuery). Every injected file must also be listed in `web_accessible_resources`.
- **Entry point.** `js/glotdict.js` runs last and calls the feature functions (`gd_add_project_links()`, `gd_pagination()`, …). Each one checks whether the current page applies and returns otherwise.
- **Same-origin requests.** Because the scripts run in the page, a `fetch()` to translate.wordpress.org carries the moderator's session cookies. No extension permissions are needed.
- **Release.** `pack-ext.py` builds a Firefox `.xpi` from the repo as is, and a Chrome `.zip` after converting the manifest to V3. `.github/workflows/deploy.yml` publishes both to the stores on `v*` tags (upstream only).

### Manifest V2 and Chrome

The repo's `manifest.json` is **Manifest V2** (`"background": {"scripts": [...]}`), which is the Firefox format. Current Chrome no longer runs Manifest V2 extensions, unpacked ones included, so the repo folder can't be loaded in Chrome as it is. Options for testing in Chrome:

- Build with `python3 pack-ext.py .`, unzip the Chrome `.zip` into a separate folder and load that folder unpacked. Repeat after every pull.
- Or test in Firefox: `about:debugging` > This Firefox > Load Temporary Add-on > the repo's `manifest.json`. It loads the repo folder directly, but it is removed when Firefox closes.

To decide with Nilo. Don't change the manifest in the feature branch: that is upstream's decision, not part of this feature.

## How the feature fits in

To keep the diff small:

- **New files:** `js/glotdict-waiting-links.js` (all the feature's logic) and, if needed, a few rules at the end of `css/style.css`.
- **Existing files, one line each:** add the script to `jsScripts` in `js/init.js` and to `web_accessible_resources` in `manifest.json`, call `gd_waiting_links()` from `js/glotdict.js`, and declare the new global in `.eslintrc`.
- The new function checks the URL and returns right away on any other page.

## What the feature does

### 1. Direct links (first priority)

On the Waiting tab, and on the public locale listing filtered by waiting and fuzzy strings (`/locale/<locale>/default/<type>/?filter=strings-waiting-and-fuzzy&page=<n>`):

- For each project card on the current page (24 per page), fetch the project's page in the locale, `/locale/<locale>/default/<type>/<slug>/`, in the background.
- From its `table.locale-sub-projects` table (one row per subproject, with columns Translated, Fuzzy, Untranslated, Waiting and Changes requested), take the subprojects with waiting or fuzzy strings, their counts and the links of those counts. Those links already point to the subproject filtered by that state, for example `/projects/wp-plugins/newsletter/stable/es/default/?filters[translated]=yes&filters[status]=waiting`.
- Add those links to the project card, so the moderator goes straight to the strings.

### 2. Export the URLs (second priority)

A button on the same page that exports the subproject URLs of the current page (about 40 for 24 projects), so the moderator can feed them to a local translation tool, then review and upload the translations. Human in the loop, not full automation.

### Rules

- **Only the page being viewed.** Never walk the whole list (600+ projects for `es`). The load is the same as opening each card by hand: 24 requests per page, sent one or a few at a time, not all at once. WordPress.org rate-limits and may answer with a CAPTCHA.
- **The moderator's own session.** Same-origin requests from the page, with the moderator's cookies and permissions. The public pages and API have no CORS headers.
- **The page stays as it is.** Keep the moderator's sort, filters and pagination; only add to the cards.
- **Nothing to store.** No cache, no backend, no account.

### Who it is for

Translation editors of a locale: GTEs (the whole locale) and PTEs (specific projects). They review **waiting** strings (suggested, pending review) and **fuzzy** strings (marked as doubtful, usually because the original changed slightly). *Changes requested* strings are out of scope.

On translate.wordpress.org, themes have a single set of strings (`/projects/wp-themes/<slug>/<locale>/default/`); plugins usually have four subprojects (`/projects/wp-plugins/<slug>/<subproject>/<locale>/default/`: `stable`, `dev`, `stable-readme`, `dev-readme`). The Waiting tab lists *projects*, so today a moderator opens the project, then the subproject, then filters by state: two extra clicks per project.

## Technical notes

- Parse the fetched pages with `DOMParser`.
- Project cards carry the project as a CSS class, `project-wp-plugins-<slug>` (and `project-wp-themes-<slug>`), as seen on the public listing.
- The GlotPress API also has public data that may be useful later: `/api/projects/<type>/<slug>/<subproject>/<locale>/default/?filters[status]=waiting` lists the waiting strings with author and dates (for prioritizing by age), and `.../export-translations/?format=po&filters[status]=waiting` exports them as a PO file. Don't call `/api/languages/<locale>/default`: it takes about 30 seconds and returns nothing.

## To confirm before writing code

- **The Waiting tab's URL and markup.** It was never seen logged in. Nilo has to save its HTML so the selectors are right. The server builds it with the same query as the public listing (`get_paged_active_sub_projects()` in `wporg-gp-routes/inc/routes/class-locale.php` in the Meta repository), so the cards are probably the same.
- **How Nilo tests in Chrome** (see "Manifest V2 and Chrome").

## Open questions

- Export format: base subproject URLs or URLs filtered by state; plain text, CSV or JSON; copy to clipboard or download a file. It depends on the local tool.
- Prioritization beyond the listing's own sorts (most or least waiting + fuzzy, newest or oldest).
