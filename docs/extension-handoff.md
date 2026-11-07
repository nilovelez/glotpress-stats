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
| Feature branch | `waiting-subproject-links`, created from `master` at `9a50f30` (GlotDict 2.1.6); its first commit is local only (see "Manifest V2 and Chrome") |

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

Keep that clone on the feature branch: the browser loads whatever is checked out. Load the folder in Chrome with `chrome://extensions` > Developer mode > Load unpacked (see "Manifest V2 and Chrome"), and reload the extension after each pull.

## How GlotDict is built

Findings from reading the code at 2.1.6:

- **No build step.** Plain JavaScript with jQuery (from the translate.wordpress.org page), global `gd_*` functions, ESLint config in `.eslintrc` (globals are declared there).
- **Loading.** `manifest.json` has a single content script, `js/init.js`, on `*://translate.wordpress.org/*`. `init.js` injects the rest of the files into the page as `<script>` tags, in the order of its `jsScripts` array, so they run in the page's own context (with access to `$gp` and the page's jQuery). Every injected file must also be listed in `web_accessible_resources`.
- **Entry point.** `js/glotdict.js` runs last and calls the feature functions (`gd_add_project_links()`, `gd_pagination()`, …). Each one checks whether the current page applies and returns otherwise.
- **Same-origin requests.** Because the scripts run in the page, a `fetch()` to translate.wordpress.org carries the moderator's session cookies. No extension permissions are needed.
- **Release.** `pack-ext.py` builds a Firefox `.xpi` from the repo as is, and a Chrome `.zip` after converting the manifest to V3. `.github/workflows/deploy.yml` publishes both to the stores on `v*` tags (upstream only).

### Manifest V2 and Chrome

Upstream's `manifest.json` is **Manifest V2** (`"background": {"scripts": [...]}`), the Firefox format; `pack-ext.py` converts it to V3 only when it builds the Chrome package. Current Chrome no longer runs Manifest V2 extensions, unpacked ones included.

Decision: the **first commit of the feature branch** (`5c40d55`, "Local only: Manifest V3 to load the folder in Chrome") applies the same V3 conversion to `manifest.json`, so Nilo loads the clone folder directly in Chrome (`chrome://extensions` > Developer mode > Load unpacked) and only has to reload it after a pull. That commit is for our use only:

- Keep it as the first commit of the branch and don't put anything else in it.
- Feature changes to the manifest (adding our script to `web_accessible_resources`) go in their own commits, in the V3 format.
- `pack-ext.py` doesn't work on this branch (it expects the V2 manifest). It isn't needed.

### Opening the pull request

When Nilo asks:

1. Update `master` from `upstream/master`.
2. Create a branch for the pull request from `master` and bring the feature commits without the local-only one, for example `git rebase --onto master 5c40d55 <copy of the feature branch>`.
3. Fix the manifest by hand: our script goes in upstream's V2 `web_accessible_resources` list, one line.
4. Check that the diff only contains the feature, push the branch to the fork and open the pull request to `Mte90/GlotDict`.

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

## The Waiting tab

Confirmed from copies Nilo saved logged in (2026-11-06 and 2026-11-07):

- URL: `/locale/<locale>/default/waiting/`, paginated with `?page=<n>` (24 projects per page), with the same filter select as the listings and a "Limit to projects without editors" checkbox.
- For translation editors (GTE and PTE), the Waiting tab is also the **default tab of the locale page**, `/locale/<locale>/default/`, paginated as `/locale/<locale>/default/?page=<n>`. Same cards and breadcrumb; in the tab bar, `<a href="/locale/<locale>/default/waiting/" class="current">` marks it as active on both URLs. For other users, the locale page opens another tab.
- Each project is a `div.project.project-waiting.project-<type>-<slug>` inside `#projects`, the same card as the public listings. `.project-name a` links to the project page in the locale; `.project-bottom` holds the "Translate Project" button.

## Implementation (milestone 4)

Commits `ec3f624`, `e819a6c`, `1f70b4e` and `cfbc6a8` on the feature branch:

- `js/glotdict-waiting-links.js`: `gd_waiting_links()` runs only under `/locale/`, and there on the Waiting tab, detected by the active tab (`a.current` whose `href` ends in `/waiting/`, whatever the URL, locale or set), and on listings whose `filter` starts with `strings-waiting-and-fuzzy`. For each card it fetches the `.project-name a` page, two at a time, parses `table.locale-sub-projects` with `DOMParser`, and adds a box before `.project-bottom` with a table of the sub-projects that have waiting or fuzzy strings:
  - Rows: the sub-project name, plain text and without the note in parentheses ("Stable", "Stable Readme", "Development", "Development Readme").
  - Columns Waiting and Fuzzy: the count links to the filtered strings; the cell is empty when there are none.
  - The box shows a loading message, an error message if the fetch fails, or a note when no sub-project has pending strings.
  - It adds the `gd-waiting-links-page` class to `body`, used to hide `.project-bottom` (the "Translate Project" button) and `.project-description` on these pages, at Nilo's request.
  - The table has its own zebra rows: it isn't a `.gp-table`, and its cells reset the page's table styles.
  - Elements are built with the DOM, never with `innerHTML`.
- One line each in `js/init.js`, `manifest.json`, `js/glotdict.js` (the call goes after `gd_pagination()`) and `.eslintrc`, plus a block at the end of `css/style.css`.
- Checked with ESLint 8 and GlotDict's `.eslintrc` (clean), and in headless Chrome against the saved Waiting tab with two saved project pages.

## Open questions

- Export format: base subproject URLs or URLs filtered by state; plain text, CSV or JSON; copy to clipboard or download a file. It depends on the local tool.
- Prioritization beyond the listing's own sorts (most or least waiting + fuzzy, newest or oldest).
