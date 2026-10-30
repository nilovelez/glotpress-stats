# Status

Version 0.7 of the plugin is in production: the `[glotstats]` shortcode shows translators which popular plugins and themes of a locale still have untranslated strings. A view for translation editors was researched and moved to a separate project, a browser extension, because translate.wordpress.org has no bulk source for per-subproject waiting counts. Next steps for the plugin are in `docs/BACKLOG.md`, pending Nilo's choice.

## Log

Newest entries first. One per work session, brief.

- 2026-10-30: researched translate.wordpress.org data sources for a moderator view (public listing, project pages, GlotPress API, REST API, Meta source code); no bulk source exists, so the moderator view moves to a browser extension project. Documented in `docs/research-data-source.md` and `docs/moderator-view.md`.
- 2026-10-30: development environment set up (Composer with WPCS and DataTables, PHPCS, Playground blueprint, release exclusions), runtime files moved to `resources/`, agent docs, handoff and backlog written, and the moderator view defined. Next: research the data source.
- 2026-10-30: guppy/STATUS.md created.
