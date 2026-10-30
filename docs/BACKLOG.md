# Backlog

Known issues and improvements, not scheduled yet. The plugin works and these are not a priority: it is a small working tool for translate.wordpress.org volunteers, used by very few people. Pick items from here only when Nilo schedules them.

## Performance

- **No caching.** Every page view fetches the stats page from translate.wordpress.org, and the `?rand=` parameter bypasses any cache on purpose. This is a deliberate choice for now: fresh data matters more than server performance, since the plugin only reads and parses one HTML page. If caching is ever added, keep it short (a transient of a few minutes) so the data stays fresh.

## Bugs

- **Fetch errors are not shown.** When `wp_remote_get()` fails, `parse()` returns an error message, but the shortcode callback ignores the return value, so the page shows nothing.
- **Top 100 and Top 200 counts are off by one.** `$i` is zero-based and the checks are `$i <= 100` and `$i <= 200`, so they count 101 and 201 projects.
- **Two shortcodes on the same page break the JavaScript.** Each shortcode adds `const GLOTSTATS_COPY` (and, in the `top` view, `const GLOTSTATS_TRANSLATION`) with `wp_add_inline_script()`, so the constants are declared twice and the script fails. The table id `stats-table` is also duplicated.
- **`data-slug` is not escaped.** The `top` view prints `$row['slug']` in the `data-slug` attribute without `esc_attr()`.
- **DataTables version mismatch.** The bundled file is DataTables 2.3.8, but it is registered as `2.3.7`.

## Behavior to review

- **Exclusions are manual.** Projects that use their own translation platform are filtered out only through the shortcode's `exclude` attribute; there is no automatic detection.
- **Demo data exists only for `es`.** In `local` and `development` environments the plugin reads `demo_data/top_<directory>_es.html` whatever the locale.

## Code quality

- **WordPress Coding Standards.** `composer lint` reports 23 errors and 15 warnings in the existing code.
