# Backlog

Known issues and improvements, not scheduled yet. The plugin works: it is a small working tool for translate.wordpress.org volunteers, used by very few people. Pick items from here only when Nilo schedules them.

The backlog is empty.

## Decided against

Don't propose these again unless Nilo brings them up:

- **Caching.** Every page view fetches the stats page from translate.wordpress.org on purpose: fresh data matters more than server load.
- **Automatic exclusions.** Projects that use their own translation platform are filtered out only through the shortcode's `exclude` attribute.
- **Demo data for other locales.** In `local` and `development` environments the plugin reads the `es` demo data whatever the locale.

## Done

- 0.8: fetch errors are shown; Top 100 and Top 200 counts fixed (they counted 101 and 201); two shortcodes on the same page work (copy strings added once, language strings per table, unique ids after the first); `data-slug` escaped; DataTables registered as 2.3.8; WordPress Coding Standards clean.
