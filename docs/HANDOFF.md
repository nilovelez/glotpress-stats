# Handoff

Where the project stands, to continue in a new session. Update it at the end of every milestone and every work session. Read `AGENTS.md` first.

## Current state

- `master` holds version 0.7 of the plugin, in production, plus the development tooling. `development` is the working branch.
- The translator views (`[glotstats]`, `top` and `tasks`) work and are not being changed.
- The moderator view is defined in `docs/moderator-view.md`. No code has been written for it.

## Milestones

| # | Milestone | Status |
|---|---|---|
| 0 | Development environment: Composer (WPCS, DataTables), PHPCS, blueprint, `.gitattributes`, README, agent docs. | Done, approved |
| 1 | Research the data source for the moderator view: what translate.wordpress.org offers (public API, locale pages, logged-in pages), what needs a moderator's login, and a proposed approach. Output: `docs/research-data-source.md`. No plugin code. | In progress: public sources done |

## Decisions

- **No caching of translate.wordpress.org data for now.** Fresh data matters more than server load for this small tool. See `docs/BACKLOG.md`.
- **Fuzzy and waiting strings don't count in the translator views.** Translators can only work on untranslated strings; reviewing the rest is the moderator view's job.
- **`assets/` is reserved for WordPress.org images**; runtime files live in `resources/`.
- **DataTables comes from Composer**, pinned to an exact version, and is built into `resources/vendor/datatables/` (see `AGENTS.md`). Version 2.3.8 from Composer was checked against the files uploaded by hand: same code, only the download builder's header comment and its `builder` info string differ.

## Next step

Milestone 1 continues. The public sources are documented in `docs/research-data-source.md`: all the data is public, but a full locale needs about 700 requests. Next: compare with the logged-in Waiting tab (Nilo will provide an HTML dump), then propose an approach.
