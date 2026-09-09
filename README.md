# Queuebo — self-hosted monorepo

Docker deployment for **Queuebo** (AI social-media management platform, built on
Stackposts v10, CodeCanyon item `21747459`). One repo holds the containerisation and,
under `app/`, your **licensed** copy of the application.

```
.
├── app/                    ← application source (Stackposts base, rebranded — docs/REBRAND.md)
├── docker/
│   ├── Dockerfile          php 8.3-fpm + nginx + supervisor, one image
│   ├── entrypoint.sh       role dispatch, db wait, migrate/cache
│   ├── nginx.conf php.ini opcache.ini supervisord.conf
├── docker-compose.yml      app + queue + scheduler + mysql + redis
├── .env.docker.example
├── Makefile
├── .github/workflows/ci.yml
└── docs/
    ├── ANALYSE-CODE.md     code / stack analysis
    ├── CLEANUP.md          what was trimmed from the stock package
    ├── REBRAND.md          Stackposts → Queuebo changes
    └── DEPLOYMENT.md       full deployment guide
```

## Licensing — read first

The Stackposts base is sold under the **Envato commercial licence**. Keep this
repository **private**. `app/` holds the application source (see
`docs/CLEANUP.md` and `docs/REBRAND.md` for what was changed vs. the stock
package).

## Quick start

```bash
# 1. configure
cp .env.docker.example .env.docker      # edit APP_URL, DB_PASSWORD, ...

# 2. run
docker compose --env-file .env.docker up -d --build
#    or: make up

# 3. install
open http://localhost:8080/installer
```

In the wizard use DB host `mysql`, port `3306`, database/user `queuebo`,
password = `DB_PASSWORD`, and your Envato **purchase code** (verified against
`stackposts.com`, so the host needs outbound HTTPS).

## Services

| Service     | Role via `CONTAINER_ROLE` | Process |
|-------------|---------------------------|---------|
| `app`       | `app`       | nginx + php-fpm |
| `queue`     | `queue`     | `php artisan queue:work` |
| `scheduler` | `scheduler` | `php artisan schedule:work` |
| `mysql`     | —           | MySQL 8.0 |
| `redis`     | —           | Redis 7 |

Persistent volumes: `mysql`, `redis`, `storage` (the app's `storage/`).

## Production notes

- Set `APP_ENV=production`, `APP_DEBUG=false`, `OPTIMIZE=true`,
  `SESSION_SECURE_COOKIE=true` in `.env.docker`.
- `opcache.validate_timestamps=0` → rebuild the image to ship code changes.
- Terminate TLS in a reverse proxy in front of `app`; set `APP_URL` to the
  public `https://` origin.
- Routes are **not** cached (closure routes in `routes/web.php`); config,
  views and events are.

See [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md) for the long form.
