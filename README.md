# GlotPress Stats

A WordPress plugin that helps moderators of a translate.wordpress.org locale decide what to translate next. It adds a shortcode that fetches the locale's translation stats and shows a polyglot-oriented digest of the most popular plugins or themes that are not fully translated yet.

## Usage

```
[glotstats locale="es" directory="plugins" view="top" exclude=""]
```

| Attribute | Values | Default | Description |
|---|---|---|---|
| `locale` | Any locale slug used in translate.wordpress.org URLs | `es` | Locale to show the stats for. |
| `directory` | `plugins`, `themes` | `plugins` | Directory to read the stats from. |
| `view` | `top`, `tasks` | `top` | `top`: a sortable, searchable table of the untranslated projects in the top 200 of the directory. `tasks`: ready-to-copy Slack messages with the details of the next projects to translate. |
| `exclude` | Comma-separated project slugs | (none) | Projects to leave out of the results. |

On sites whose environment type is `local` or `development`, the plugin reads the sample data in `demo_data/` instead of fetching translate.wordpress.org.

## Development

The repo holds the plugin plus its development files, which are left out of the release zip through `.gitattributes`. The plugin's CSS and JavaScript live in `resources/`; `assets/` is reserved for the WordPress.org directory images.

- **Coding standards:** `composer install`, then `composer lint` (PHPCS with the WordPress Coding Standards) and `composer fix`.
- **Test site:** `npx @wp-playground/cli@latest server --blueprint ./blueprint.json --mount-dir . /wordpress/wp-content/plugins/glotpress-stats`, then open http://127.0.0.1:9400 (`admin` / `password`). The blueprint creates two pages with the shortcode, one per view.
- **Release zip:** `git archive --format=zip --prefix=glotpress-stats/ -o glotpress-stats-<version>.zip HEAD`.

Third-party code: [DataTables](https://datatables.net/) 2.3.7 (MIT), in `resources/vendor/datatables/`.

## License

GPLv2 or later.
