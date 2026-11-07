# Handoff

Where the project stands, to continue in a new session. Update it at the end of every milestone and every work session. Read `AGENTS.md` first.

## Current state

- `master` holds version 0.8 of the plugin, published as the `0.8` tag and GitHub release with the zip. `development` is the working branch for code; changes that don't touch the code may go straight to `master` when Nilo says so.
- **This plugin is for translators only and is in maintenance.** The translator views (`[glotstats]`, `top` and `tasks`) work and are its whole scope.
- The moderator view is being built as a feature of GlotDict, on the `waiting-subproject-links` branch of Nilo's fork (`nilovelez/GlotDict`, cloned at `~/repos/glotdict`), to become a pull request to `Mte90/GlotDict` when Nilo asks. Its docs live in this repo so the fork only gets code: `docs/extension-handoff.md`.

## Milestones

| # | Milestone | Status |
|---|---|---|
| 0 | Development environment: Composer (WPCS, DataTables), PHPCS, blueprint, `.gitattributes`, README, agent docs. | Done, approved |
| 1 | Research the data source for the moderator view. Output: `docs/research-data-source.md`. | Done: no bulk source exists; the moderator view moves to a browser extension project |
| 2 | Backlog fixes, version 0.8: fetch errors shown, Top 100/200 off-by-one, two shortcodes on one page, `data-slug` escaping, DataTables version, WPCS clean. Tested on Playground. | Done, approved and published as 0.8 |
| 3 | GlotDict setup: `upstream` remote, feature branch `waiting-subproject-links` with a local-only Manifest V3 commit to load it in Chrome, review of GlotDict's code, docs for working with the fork. | Done, approved |
| 4 | Direct subproject links on the Waiting tab, in GlotDict (`js/glotdict-waiting-links.js`). | Done, tested by Nilo in Chrome (with the table layout) |

## Decisions

- **The moderator view is a GlotDict feature, not part of this plugin.** It works on the moderator's own Waiting tab, with their session and permissions, one page at a time, and exports the subproject URLs for local translation tools. It is an addition to GlotDict that touches existing code as little as possible, developed on a branch of Nilo's fork that he uses as an unpacked extension until it is ready for a pull request.
- **Longer term, move GlotDict features into GlotPress core** where they belong there (for example, visual approve and reject buttons), working with the GlotPress team, and remove them from GlotDict.
- **Moderators review waiting and fuzzy strings only;** changes-requested strings are out of scope.
- **No caching of translate.wordpress.org data for now.** Fresh data matters more than server load for this small tool, and it won't be added. See `docs/BACKLOG.md`.
- **Fuzzy and waiting strings don't count in the translator views.** Translators can only work on untranslated strings.
- **`assets/` is reserved for WordPress.org images**; runtime files live in `resources/`.
- **DataTables comes from Composer**, pinned to an exact version, and is built into `resources/vendor/datatables/` (see `AGENTS.md`). Version 2.3.8 from Composer was checked against the files uploaded by hand: same code, only the download builder's header comment and its `builder` info string differ.

## Next step

No milestone scheduled, and the backlog is empty: Nilo decided not to do the remaining items (see `docs/BACKLOG.md`). Only maintenance from here on for the plugin.

GlotDict: work stopped after milestone 4, by Nilo's choice. The next milestone, when he schedules it, is the button to export the sub-project URLs of the current page (see "Export the URLs" and "Open questions" in `docs/extension-handoff.md`). The pull request to `Mte90/GlotDict` comes later, only when Nilo asks.

Git state of the fork at the stop: `master` = `upstream/master` = `9a50f30` (never commit to it); `waiting-subproject-links` = `b188985`, pushed, with the local-only commit `5c40d55` at its base.

Testing without Nilo's session: `/Volumes/Datos/scratch/glotdict/` holds the saved Waiting tab and locale page (as a GTE) and a small test server (`test/server.py`) that serves them with the feature's script; `test/run.sh` renders them in headless Chrome and reports where the feature ran. Scratch has no backup and can be regenerated.

Releases: bump `Version` and the asset versions in `glotpress-stats.php` whenever CSS or JS change, so browsers don't keep the old files. Tags have no `v` prefix (`0.8`); the GitHub release carries the `git archive` zip.

Test pages in `blueprint.json`: `/glotstats-top/`, `/glotstats-tasks/`, `/glotstats-two-tables/` (plugins and themes tables on one page) and `/glotstats-error/` (an invalid locale, to see the error message).
