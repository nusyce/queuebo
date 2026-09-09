# Cleanup applied to `app/`

Changes made to the stock CodeCanyon `Install.zip` tree before committing it
here. **No application logic was modified** — only dev artefacts, local
runtime state, and version-control hygiene. Diff against a fresh Envato
download to verify.

## Removed

| Path | Reason |
|---|---|
| `resources/themes/shared/plugins/codemirror5/demo/` `doc/` `test/` `src/` `bin/` | CodeMirror 5 dev material; runtime uses only `lib/ addon/ mode/ theme/ keymap/` (verified against Blade refs) |
| `…/codemirror5/**/*.html`, `**/*_test.js`, `CHANGELOG.md`, `AUTHORS`, `.eslintrc*`, `rollup.config.js`, `package.json` | not referenced at runtime — trimmed plugin from 5.6 MB → 3.0 MB |
| `.env`, `.env.backup`, `.env.production` | filled-in local secrets; `.env.example` kept, real config comes from `.env.docker` |
| `storage/logs/laravel.log` | leftover runtime log |
| `storage/framework/sessions/*` | leftover session blob |
| `bootstrap/cache/*.php` | compiled caches (none were present) |
| `public/storage/` (shipped as a real dir) | should be a symlink → `storage/app/public`; `php artisan storage:link --force` recreates it at container boot |

## Added

| Path | Reason |
|---|---|
| `storage/framework/{cache/data,sessions,testing,views}/.gitignore` | standard Laravel keepers — the package shipped only the top-level one, so the empty dirs would not survive a commit and the runtime would fail |
| `storage/logs/.gitignore` | same |

## Modified

| Path | Change |
|---|---|
| `app/.gitignore` | trimmed: `vendor/` and `public/build/` are **kept tracked** (self-contained image build; the frontend has no buildable sources in the package). Only true local state stays ignored. |

## Left intact deliberately

- All PHP under `app/ modules/ routes/ config/ database/`, all Blade views.
- `vendor/` and `public/build/` — shipped prebuilt, committed as-is
  (`.gitattributes` marks them `-text` so bytes are untouched).
- `app/Installer/` including the purchase-code verification that calls
  `stackposts.com` — not touched.
