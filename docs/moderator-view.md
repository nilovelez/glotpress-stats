# Moderator view

Specification of the new feature, as defined with Nilo. It will grow as decisions are made.

## Background: how translate.wordpress.org is organized

translate.wordpress.org runs GlotPress. Every plugin and theme in the WordPress.org directories is a project, translatable into every WordPress.org locale.

- **Themes** have a single set of strings: `/projects/wp-themes/<slug>/<locale>/default/`.
- **Plugins** usually have four subprojects: `/projects/wp-plugins/<slug>/<subproject>/<locale>/default/`.
  - `stable`: strings of the released version.
  - `dev`: strings of trunk.
  - `stable-readme`: readme of the released version.
  - `dev-readme`: readme of trunk.

Translators suggest translations; translation editors (GTE for the whole locale, PTE for specific projects) review them. Strings pending review are mainly *waiting* (suggested, not approved) and *fuzzy* (need a check, for example after the original changed).

## The problem

The existing `[glotstats]` views serve translators: they show where to translate for the most impact. Moderators have no equivalent.

In the locale page, logged-in moderators get a **Waiting** tab that lists every project with strings pending review. It only lists *projects*: to reach the strings, the moderator has to open each project and then the subproject that has them. With many projects that is slow, repetitive work.

That page cannot be parsed like the stats page: it is paginated, and it is only served to logged-in users with editor rights.

## What to build

1. **First priority: a view for moderators.** A sortable table with **one row per subproject** that has strings pending review (waiting, fuzzy, or any other state that needs a moderator), with the counts and a **direct link** to the subproject filtered by that state, for example `/projects/wp-plugins/<slug>/stable/<locale>/default/?filters[status]=waiting`.
   - No fixed priority for now: the table is sortable, like the translator view. With little time, a moderator sorts by fewest pending strings and clears many subprojects quickly; with more time, by most pending strings to review a lot in one go.
   - Later: finer prioritization, such as how long strings have been waiting (the Waiting tab can already filter by that).
2. **Second priority: input for automation.** The same list of subprojects with direct URLs lets an agent or a script know exactly what to download, review and upload, without navigating the logged-in Waiting tab.

## Open questions

- **Data source.** Where to get, for a locale, the list of subprojects with strings pending review and their counts, and whether it needs a moderator's login. This is milestone 1 (research); its results will go in `docs/research-data-source.md`.
- **Prioritization criteria**, beyond sorting by count.
