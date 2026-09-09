# Stackposts v10 — Docker deployment

Container setup for self-hosting **your licensed copy** of Stackposts
(CodeCanyon item `21747459`). These files are infrastructure only — they do
**not** contain Stackposts source. Drop them into the root of a copy you
obtained from your own Envato account.

> **Licensing:** Stackposts is sold under the Envato commercial licence. Keep
> the application source in a **private** repository tied to your deployment,
> and never redistribute it. Do not use a "nulled" build — besides the licence
> violation, those are a common malware vector.

---

## Layout

```
stackposts/  (repo root)
├── Dockerfile                 # from this folder
├── docker-compose.yml         # from this folder
├── .dockerignore              # from this folder
├── .env.docker                # you create it from .env.docker.example
├── docker/
│   ├── entrypoint.sh
│   ├── nginx.conf
│   ├── php.ini
│   ├── opcache.ini
│   └── supervisord.conf
├── app/  modules/  public/  vendor/  ...   # the Stackposts application
```

`vendor/` and `public/build/` ship prebuilt in the CodeCanyon `Install.zip`,
so no Composer or Node build step runs by default.

---

## Image

Single runtime image, `php:8.3-fpm-bookworm` based, running **nginx +
php-fpm** under supervisor. PHP extensions: `pdo_mysql mbstring exif pcntl
bcmath gd zip intl sockets opcache redis imagick`.

The same image backs three compose services, switched by `CONTAINER_ROLE`:

| Service     | Role        | Process                              |
|-------------|-------------|-------------------------------------|
| `app`       | `app`       | nginx + php-fpm (HTTP)              |
| `queue`     | `queue`     | `php artisan queue:work`            |
| `scheduler` | `scheduler` | `php artisan schedule:work`         |

Plus `mysql:8.0` and `redis:7`.

---

## First run

```bash
# 1. copy these files into the project root, then:
cp .env.docker.example .env.docker
#    edit .env.docker — at minimum set APP_URL and the DB_* passwords

# 2. build + start
docker compose --env-file .env.docker up -d --build

# 3. generate the app key (only if APP_KEY is empty)
docker compose exec app php artisan key:generate --force

# 4. open the installer
#    http://localhost:8080/installer
```

In the web installer:

* **Purchase code** — your Envato purchase code (verified against
  `stackposts.com`; the server must have outbound HTTPS).
* **Database** — host `mysql`, port `3306`, database `stackposts`, user
  `stackposts`, password = `DB_PASSWORD` from `.env.docker`.
* **Admin account** — your credentials.

The wizard runs migrations and sets `APP_INSTALLED=true` inside the
container's `.env`. To make that persistent across `docker compose down`,
either bind-mount `.env` or bake the finished `.env` into `.env.docker`.

### Already-installed database

Skip the wizard: set `RUN_MIGRATIONS=true` in `.env.docker` and point `DB_*`
at your existing database.

---

## Steady-state production

Set in `.env.docker`:

```
APP_ENV=production
APP_DEBUG=false
OPTIMIZE=true          # cache config/views (restart to pick up .env changes)
SESSION_SECURE_COOKIE=true
```

`opcache.validate_timestamps=0` — **code changes require an image rebuild +
`docker compose up -d`**, not just a file edit.

Put a TLS-terminating reverse proxy (Traefik, Caddy, nginx, a load balancer)
in front of the `app` service and set `APP_URL` to the public `https://` URL.

---

## Common commands

```bash
docker compose exec app php artisan about
docker compose exec app php artisan migrate --force
docker compose logs -f app queue scheduler
docker compose exec mysql mysqldump -ustackposts -p stackposts > backup.sql
docker compose down            # keep volumes
docker compose down -v         # also drop db + storage volumes
```

Persistent volumes: `mysql` (database), `redis` (AOF), `storage`
(`storage/` — uploads, logs, sessions, cache).

---

## Notes / gotchas

* **Health check** uses Laravel's `/up` route. If you removed it, drop the
  `healthcheck` block from the `app` service.
* **Routes are not cached** (`routes/web.php` has closure routes that Laravel
  cannot serialise); config/view/event caching is used instead.
* **Queues** default to the `database` driver to match Stackposts' shipped
  config. Switch `QUEUE_CONNECTION=redis` in `.env.docker` if preferred — the
  `redis` service is already running.
* **Outbound network** — the installer's purchase-code check, social-network
  APIs and payment gateways all need egress from the containers.
* **File uploads** capped at 128 MB (`php.ini` + `client_max_body_size`);
  raise both if you need more.
