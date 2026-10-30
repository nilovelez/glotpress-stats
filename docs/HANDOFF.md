# Handoff

Where the project stands, to continue in a new session. Update it at the end of every milestone and every work session. Read `AGENTS.md` first.

## Current state

- `master` holds version 0.8 of the plugin, published as the `0.8` tag and GitHub release with the zip. `development` is the working branch.
- The translator views (`[glotstats]`, `top` and `tasks`) work and are the scope of this plugin.
- The moderator view was researched and moved to a separate project, a browser extension (see `docs/moderator-view.md`). It will not be built here.

## Milestones

| # | Milestone | Status |
|---|---|---|
| 0 | Development environment: Composer (WPCS, DataTables), PHPCS, blueprint, `.gitattributes`, README, agent docs. | Done, approved |
| 1 | Research the data source for the moderator view. Output: `docs/research-data-source.md`. | Done: no bulk source exists; the moderator view moves to a browser extension project |
| 2 | Backlog fixes, version 0.8: fetch errors shown, Top 100/200 off-by-one, two shortcodes on one page, `data-slug` escaping, DataTables version, WPCS clean. Tested on Playground. | Done, approved and published as 0.8 |

## Decisions

- **The moderator view is a browser extension, not part of this plugin.** It works on the moderator's own Waiting tab, with their session and permissions, one page at a time, and exports the subproject URLs for local translation tools. Preferably a fork of and pull request to an existing extension (GlotDict or wpgp-tools).
- **Moderators review waiting and fuzzy strings only;** changes-requested strings are out of scope.
- **No caching of translate.wordpress.org data for now.** Fresh data matters more than server load for this small tool. See `docs/BACKLOG.md`.
- **Fuzzy and waiting strings don't count in the translator views.** Translators can only work on untranslated strings.
- **`assets/` is reserved for WordPress.org images**; runtime files live in `resources/`.
- **DataTables comes from Composer**, pinned to an exact version, and is built into `resources/vendor/datatables/` (see `AGENTS.md`). Version 2.3.8 from Composer was checked against the files uploaded by hand: same code, only the download builder's header comment and its `builder` info string differ.

## Next step

No milestone scheduled. The remaining backlog items are features, not fixes. The browser extension is a separate project with its own repo.

Releases: bump `Version` and the asset versions in `glotpress-stats.php` whenever CSS or JS change, so browsers don't keep the old files. Tags have no `v` prefix (`0.8`); the GitHub release carries the `git archive` zip.

Test pages in `blueprint.json`: `/glotstats-top/`, `/glotstats-tasks/`, `/glotstats-two-tables/` (plugins and themes tables on one page) and `/glotstats-error/` (an invalid locale, to see the error message).
