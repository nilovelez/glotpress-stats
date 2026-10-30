# Backlog

Known issues and improvements, not scheduled yet. The plugin works and these are not a priority: it is a small working tool for translate.wordpress.org volunteers, used by very few people. Pick items from here only when Nilo schedules them.

## Performance

- **No caching.** Every page view fetches the stats page from translate.wordpress.org, and the `?rand=` parameter bypasses any cache on purpose. This is a deliberate choice for now: fresh data matters more than server performance, since the plugin only reads and parses one HTML page. If caching is ever added, keep it short (a transient of a few minutes) so the data stays fresh.

## Features

- **Exclusions are manual.** Projects that use their own translation platform are filtered out only through the shortcode's `exclude` attribute; there is no automatic detection.
- **Demo data exists only for `es`.** In `local` and `development` environments the plugin reads `demo_data/top_<directory>_es.html` whatever the locale.

## Done

- 0.8: fetch errors are shown; Top 100 and Top 200 counts fixed (they counted 101 and 201); two shortcodes on the same page work (copy strings added once, language strings per table, unique ids after the first); `data-slug` escaped; DataTables registered as 2.3.8; WordPress Coding Standards clean.
