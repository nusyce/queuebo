# Rebrand — Stackposts → Queuebo

Applied over the cleaned `app/` tree. User-facing name, technical identifiers
and container naming only; no business logic changed.

## Changed

| Area | From → To |
|---|---|
| App name (`APP_NAME`, `.env*`) | `Stackposts` → `Queuebo` |
| `config('app.name', …)` fallbacks (~15 files) | `'Stackposts'` / `'StackPosts'` → `'Queuebo'` |
| Session cookie | derives from name → `queuebo-session`; installer cookie `stackposts_installer_session` → `queuebo_installer_session` |
| Cache prefix | derives from name → `queuebo-cache-` |
| DB name / user (`.env*`, compose) | `stackposts` → `queuebo` |
| User-visible Blade strings ("Connect Stackposts with…", pricing copy, empty states, payment gateway address strings, mail sender placeholder) | `Stackposts` → `Queuebo` |
| Plan seeder class + file | `StackPostsPlanSeeder` → `QueueboPlanSeeder` (+ ref in `app/Installer/config/installer.php`) |
| Automation webhook / API headers | `X-Stackposts-Event\|Key\|Signature\|Timestamp\|Request-Id` → `X-Queuebo-…` (dispatcher, key service, and the docs shown in the UI — all consistent) |
| Highcharts JS glue | `window.StackpostsHighcharts` → `QueueboHighcharts`, `__stackpostsHighchartsLoader` → `__queueboHighchartsLoader`, `__stackpostsChart` → `__queueboChart`, event `stackposts:highcharts-ready` → `queuebo:highcharts-ready`, `data-stackposts-highcharts-cdn` → `data-queuebo-highcharts-cdn` (head partial + shared JS + `highchart.blade.php`, together) |
| Theme metadata | `theme.json` `author` / `description` → Queuebo |
| Docker | image `stackposts:10` → `queuebo:10`; compose project `name: queuebo` (containers `queuebo-app-1`, …) |
| Example handles (TikTok connect, YouTube tags) | `stackposts` → `queuebo` |

## Deliberately NOT changed

| Item | Why |
|---|---|
| `https://stackposts.com/api/marketplace/install` (`installer.php`) | upstream licence-verification server |
| `AdminMarketplace` module — `STACKPOSTS_BASE_URL`, `https://stackposts.com`, "loaded from Stackposts API" | calls the upstream marketplace API; renaming breaks it |
| `/v12/stackposts/portal/teams` (`SwitchWorkspaceController`) | legacy inbound-URL compatibility shim |
| `LOG_CHANNEL=stack` | Monolog "stack" driver — unrelated to the brand |
| Author copyright headers inside third-party `vendor/` code | not ours to alter |
| Logos / favicon (`public/favicon.*`, `apple-touch-icon.png`, theme logo images) | need real artwork — upload yours in **Admin → Settings → Appearance** after install; the themes already fall back to `APP_NAME` text when no logo is set |

## Re-applying after an upstream update

`git log --stat` for the rebrand commit shows every file. The changes are
mechanical `sed` substitutions; `scripts/` is not kept, but the table above is
enough to reproduce them.
