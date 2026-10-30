# Moderator view

**Decision (2026-10-30): the moderator view will not be built in this plugin.** It moves to a separate project: Nilo's own Chrome extension for translate.wordpress.org, and later, optionally, a contribution of the same features to GlotDict (fork and pull request). This plugin keeps its translator views. The brief for the new project is `docs/extension-handoff.md`; this document keeps the definition and the reasoning.

## Background: how translate.wordpress.org is organized

translate.wordpress.org runs GlotPress. Every plugin and theme in the WordPress.org directories is a project, translatable into every WordPress.org locale.

- **Themes** have a single set of strings: `/projects/wp-themes/<slug>/<locale>/default/`.
- **Plugins** usually have four subprojects: `/projects/wp-plugins/<slug>/<subproject>/<locale>/default/`.
  - `stable`: strings of the released version.
  - `dev`: strings of trunk.
  - `stable-readme`: readme of the released version.
  - `dev-readme`: readme of trunk.

Translators suggest translations; translation editors (GTE for the whole locale, PTE for specific projects) review them. For moderators, two states matter:

- **Waiting**: translations someone suggested, pending review.
- **Fuzzy**: strings marked as doubtful, usually because a small change in the plugin's code changed the original.

*Changes requested* strings are not reviewed by moderators and are out of scope.

## The problem

The existing `[glotstats]` views serve translators: they show where to translate for the most impact. Moderators have no equivalent.

In the locale page, logged-in moderators get a **Waiting** tab that lists every project with waiting or fuzzy strings that they can approve. It only lists *projects*: to reach the strings, the moderator has to open each project and then the subproject that has them. With many projects that is slow, repetitive work.

## Goals

1. **First priority: faster review.** For each project in the list, show the subprojects that have waiting or fuzzy strings, with their counts and a **direct link** to the subproject filtered by that state, for example `/projects/wp-plugins/<slug>/stable/<locale>/default/?filters[status]=waiting`.
2. **Second priority: input for local tools.** Export the list of subproject URLs of the current page, so a moderator can feed them to a local translation tool and then review and upload the translations. Human in the loop, not full automation.

## Why not in this plugin

See `docs/research-data-source.md`. There is no bulk source: the per-subproject breakdown is only available one project at a time. A plugin-side view would have to fetch the listing plus one page per project on the server, from the public listing (which ignores the moderator's permissions), and needs a cache.

## The chosen approach: browser extension

An extension (or, to prototype, a userscript) that runs on the moderator's own Waiting tab or locale listing:

- Uses the moderator's session and permissions: a PTE only sees their projects, and filters such as `without-editors` work.
- For the projects on the current page (24), fetches each project's page in the locale (`/locale/<locale>/default/<type>/<slug>/`) in the background, from the same origin, and rewrites each project card to show the subprojects with waiting or fuzzy strings and their direct links.
- Adds a button to export the subproject URLs of the current page (about 40 for a page of 24 projects).
- The moderator keeps their usual page, sort and filters. The load is the same as opening each card by hand, and there is nothing to cache.

Pagination is deliberate: the extension only works on the page being viewed, never on the whole list (600+ projects for `es`).

Candidates to fork and contribute to: [GlotDict](https://github.com/Mte90/GlotDict) and [wpgp-tools](https://github.com/vlad-timotei/wpgp-tools). GlotDict is the likely choice: it is the extension most moderators use. Confirming it is the first step of the new project.

## Open questions for the new project

- Export format: base subproject URLs or URLs filtered by status, plain text or CSV; it depends on the local tool.
- Prioritization beyond the listing's own sorts (most or least waiting + fuzzy, newest or oldest).
