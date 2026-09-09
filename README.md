# Stackposts — self-hosted monorepo

Docker deployment for **Stackposts v10** (AI social-media management platform,
CodeCanyon item `21747459`). One repository holds the containerisation and,
under `app/`, your **licensed** copy of the application.

```
.
├── app/                    ← your licensed Stackposts source (see app/README.md)
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
    └── DEPLOYMENT.md       full deployment guide
```

## Licensing — read first

Stackposts is sold under the **Envato commercial licence**. Keep this repo
**private** and fill `app/` from your own CodeCanyon / Envato download. The
container files here contain no Stackposts source.

## Quick start

```bash
# 1. put your licensed copy in app/  (see app/README.md)
unzip /path/to/Install.zip -d app/

# 2. configure
cp .env.docker.example .env.docker      # edit APP_URL, DB_PASSWORD, ...

# 3. run
docker compose --env-file .env.docker up -d --build
#    or: make up

# 4. install
open http://localhost:8080/installer
```

In the wizard use DB host `mysql`, port `3306`, database/user `stackposts`,
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
