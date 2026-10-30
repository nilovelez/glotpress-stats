# GlotPress Stats: agent instructions

Instructions for any agent working on this repo. Read this file, then `docs/HANDOFF.md`, before doing anything.

## The project

A WordPress plugin with tools for the translate.wordpress.org (GlotPress) community of a locale:

- **Translator views (existing, in production).** The `[glotstats]` shortcode parses the public stats page of a locale (`https://translate.wordpress.org/locale/<locale>/default/stats/<plugins|themes>/`) and lists the most installed projects that still have untranslated strings, so volunteers know where their work has the most impact. See `README.md`.
- **Moderator view (in definition).** A view for translation editors (GTE and PTE) that lists the *subprojects* with strings waiting for review, with direct links, and later feeds automation. See `docs/moderator-view.md`.

The plugin is in use on a live site. Keep the translator views working as they are unless Nilo asks otherwise.

## Working rules

- **Language.** Talk to Nilo in Spanish. Everything in the repo (code, comments, user-facing strings, docs, commit messages) is in US English.
- **Milestones.** Work goes in milestones. Never start a milestone until Nilo confirms it. When one is done, summarize it, ask Nilo to review it, and wait for his OK before starting the next.
- **Branches.** `master` is the stable version, the one in production. All work happens on `development`, with no feature branches: commit and push `development` as you go, so Nilo can sync it on other computers. Publish to `master` (`git merge --no-ff development`, then push) only when Nilo asks, usually after he approves a milestone. Changes that don't touch the plugin's code (docs, tooling) may be merged to `master` when Nilo says so.
- **Docs live in the repo.** Keep `docs/HANDOFF.md` up to date at the end of every milestone and every work session, so a new session can continue without this one. Unscheduled ideas and known issues go to `docs/BACKLOG.md`.
- **Workflow.** Follow the `wp-agentic-workflow` skill (WordPress Coding Standards, Playground and Studio test sites, release with `git archive`). Test sites use `admin` / `password`; always give Nilo the URL, and the hosts line with this computer's current LAN IP for Studio domains.

## Repo layout

| Path | Content | In the release zip |
|---|---|---|
| `glotpress-stats.php` | Main plugin file: hooks, assets, shortcode. | Yes |
| `parser.php` | Fetches and parses the stats page, renders the views. | Yes |
| `resources/` | Runtime CSS and JS. `resources/vendor/` holds built third-party files. | Yes |
| `languages/` | Translations (the plugin is not on WordPress.org). | Yes |
| `assets/` | Reserved for WordPress.org directory images. Never put runtime files here. | No |
| `demo_data/` | Sample stats pages, read instead of the live site when the environment type is `local` or `development`. | No |
| `docs/` | Project docs: handoff, backlog, specs, research. | No |
| `tools/` | Development scripts. | No |
| `guppy/` | Project status for Guppy (see below). | No |

`vendor/` belongs to Composer and is ignored by Git. What is excluded from the zip is defined in `.gitattributes`.

## Development

```bash
composer install          # WPCS, DataTables, and rebuilds resources/vendor/datatables/
composer lint             # PHPCS with the WordPress Coding Standards
composer fix              # phpcbf
composer build-datatables # rebuild resources/vendor/datatables/ from vendor/
npx @wp-playground/cli@latest server --blueprint ./blueprint.json --mount-dir . /wordpress/wp-content/plugins/glotpress-stats
git archive --format=zip --prefix=glotpress-stats/ -o glotpress-stats-<version>.zip HEAD
```

### DataTables

DataTables comes from Composer (`datatables.net/datatables.net` and `datatables.net/datatables.net-dt`, pinned to an exact version). `tools/build-datatables.php` combines the core library and its default styling into `resources/vendor/datatables/datatables{.min}.{js,css}`, the same files the DataTables download builder produces, and those built files are committed and shipped. To update DataTables:

1. Change both versions in `composer.json` and run `composer update datatables.net/*`.
2. Update the version in the two `wp_register_*( 'datatables', ... )` calls in `glotpress-stats.php`.
3. Check the `top` view on a test site, and commit the rebuilt files.

## Project status (Guppy)

This project publishes its status to Guppy, Nilo's project manager (https://github.com/manuelstone/guppy). Everything Guppy-related lives in `guppy/`, written in English:

- `guppy/STATUS.md`: extended status and work log, for agents working on this project. It is never synced anywhere.
- `guppy/STATUS.json`: public summary, following the status contract (https://github.com/manuelstone/guppy/blob/main/docs/status-contract.md). `.github/workflows/guppy-sync.yml` copies it to Guppy every time it changes on the default branch, and from there it goes to a public website. Never put private information, credentials or secrets in it.

At the end of every work session on the project, even if `status` stays the same:

1. Update the Status section of `guppy/STATUS.md` and add a brief entry at the top of its Log.
2. Update `guppy/STATUS.json`: change `status` if it changed, rewrite `status_text` so it says where the project stands now, and always take new `last_update` and `last_update_unix`. They record when the status was last reported, not when `status` last changed: an `active` project gets new dates after every session. Take both dates from a single timestamp: `ts=$(date +%s); date -d "@$ts" '+%Y-%m-%d %H:%M:%S %z'; echo "$ts"` (GNU date; the contract has the macOS and PowerShell versions).
3. Commit both files with the rest of the session's changes and push to the default branch.

Statuses: `active` (being worked on), `blocked` (waiting for something outside the project), `inactive` (stopped by choice), `planned` (decided, not started), `archived` (closed).

`status_text`: one or two sentences, under 200 characters, plain text. Say where the project stands and what comes next; if blocked, start with what it is waiting for and from whom. Self-contained: no relative dates, name versions and third parties. Rewrite it in full each time; the history goes in the Log.

When Nilo asks to change this project's data in Guppy (its icon, title, description or status), edit `guppy/STATUS.json` following the contract, commit and push to the default branch: the sync publishes it. A change of only the icon, title or description does not touch the dates. `icon` is any public URL, ideally a square 128×128 PNG. Never edit the Guppy repo for this.

Do not use CHANGELOG.md for this: it is for users. Do not edit `guppy-sync.yml`. If the Guppy sync action fails, read its log in the Actions tab and tell Nilo.

The default branch is `master`: the sync only runs when `guppy/STATUS.json` reaches `master`, so status updates made on `development` are published with the next merge.
