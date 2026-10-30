# Status

Version 0.8 of the plugin is published (GitHub release `0.8`): the `[glotstats]` shortcode shows translators which popular plugins and themes of a locale still have untranslated strings. No milestone is scheduled and the backlog is empty. The view for translation editors moved to a separate project, a browser extension.

## Log

Newest entries first. One per work session, brief.

- 2026-10-30: version 0.8 approved, merged to `master` and published as a GitHub release.

- 2026-10-30: version 0.8 on `development`: fetch errors shown, Top 100/200 counts fixed, two shortcodes on one page fixed, `data-slug` escaped, DataTables version corrected, WPCS clean. Tested on Playground.

- 2026-10-30: researched translate.wordpress.org data sources for a moderator view (public listing, project pages, GlotPress API, REST API, Meta source code); no bulk source exists, so the moderator view moves to a browser extension project. Documented in `docs/research-data-source.md` and `docs/moderator-view.md`.
- 2026-10-30: development environment set up (Composer with WPCS and DataTables, PHPCS, Playground blueprint, release exclusions), runtime files moved to `resources/`, agent docs, handoff and backlog written, and the moderator view defined. Next: research the data source.
- 2026-10-30: guppy/STATUS.md created.
