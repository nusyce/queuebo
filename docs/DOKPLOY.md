# Queuebo on Dokploy

Deploy the app stack (`app` + `queue` + `scheduler`) as a single **Compose**
service in [Dokploy](https://dokploy.com). Dokploy's bundled Traefik handles
the domain and TLS; everything else runs from
[`docker-compose.dokploy.yml`](../docker-compose.dokploy.yml).

**MySQL and Redis are not part of this stack.** They're expected to be
Dokploy **native _Database_ services** (Create Service → Database), which
Dokploy manages — credentials, storage, versions, backups. Create one MySQL
and one Redis (or reuse existing ones), then point `DB_HOST` / `REDIS_HOST`
from the Environment tab at the **Internal Host** shown in each database's
*Internal Connection* section. The `app` / `queue` / `scheduler` containers
reach them over the shared `dokploy-network`.

See [`Nusyce-Repo/dokploy`](https://github.com/Nusyce-Repo/dokploy) for the
cluster-side setup (native DBs, pgAdmin/phpMyAdmin, Vaultwarden, OpenBao).

---

## Prerequisites

- A server with Dokploy installed (`curl -sSL https://dokploy.com/install.sh | sh`).
  This creates the external `dokploy-network` and the Traefik container the
  compose file expects.
- DNS: an `A`/`AAAA` record for your domain (e.g. `queuebo.example.com`)
  pointing at the server.
- Git access to this repository from the Dokploy server (deploy key or PAT
  for a private repo).

---

## 1. Create the service

1. **Project → Create Service → Compose.**
2. **Provider:** this Git repository + branch (`main`).
3. **Compose Path:** `./docker-compose.dokploy.yml`
4. **Compose Type:** `docker-compose` (build support — the image is built
   from `docker/Dockerfile` on the server).

## 2. Environment

Open the **Environment** tab and paste
[`.env.dokploy.example`](../.env.dokploy.example), then set at least:

| Variable            | Notes                                                        |
|---------------------|-------------------------------------------------------------|
| `APP_DOMAIN`        | bare host, no scheme — `queuebo.example.com`. `APP_URL` is derived as `https://$APP_DOMAIN`. |
| `APP_KEY`           | `php artisan key:generate --show` (or `docker run --rm queuebo:dokploy php artisan key:generate --show` after the first build). Keep it stable. |
| `DB_HOST`           | **Internal Host** of your Dokploy native MySQL (from its *Internal Connection* section). `DB_PORT` defaults to `3306`. |
| `DB_DATABASE` / `DB_USERNAME` | dedicated db + user — create them once via Dokploy → MySQL → *Execute SQL* (see below). Default `queuebo` / `queuebo`. |
| `DB_PASSWORD`       | that user's password.                                        |
| `REDIS_HOST`        | **Internal Host** of your Dokploy native Redis. `REDIS_PORT` defaults to `6379`; set `REDIS_PASSWORD` from its page (or leave `null`). |
| `ADMIN_EMAIL`       | first admin account, created on first boot.                  |

Leave `ADMIN_PASSWORD` blank to have a strong one generated and printed to
the `app` container log.

Create the database and user once (Dokploy → your MySQL → **Execute SQL**):

```sql
CREATE DATABASE queuebo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'queuebo'@'%' IDENTIFIED BY 'a-strong-password';
GRANT ALL PRIVILEGES ON queuebo.* TO 'queuebo'@'%';
```

Redis needs no setup — pick an unused database number if you set
`QUEUE_CONNECTION=redis` / `CACHE_STORE=redis` and want isolation.

## 3. Domain

**Domains** tab → Add:

- **Host:** `queuebo.example.com`
- **Service Name:** `app`
- **Container Port:** `80`
- **HTTPS:** on, **Certificate:** Let's Encrypt

The compose file also carries Traefik labels for the same result, so the
site comes up even before you touch this tab — the UI entry just makes the
domain visible/manageable in Dokploy. Use one or the other; if you set the
domain in the UI you can delete the `labels:` block on the `app` service.

## 4. Deploy

Hit **Deploy**. First boot sequence:

1. image builds from `docker/Dockerfile` (PHP 8.3 + nginx + supervisor);
2. `app` waits for the native MySQL to accept connections (`WAIT_FOR_DB`);
3. `app` runs `php artisan queuebo:install` — migrations, seeders (plans, AI
   templates), the admin user — then flips `APP_INSTALLED=true`. Idempotent:
   later deploys skip it;
4. `queue` and `scheduler` wait for the `migrations` table, then start
   `queue:work` / `schedule:work`;
5. Traefik issues the certificate and routes `https://$APP_DOMAIN` → `app:80`.

Watch **Logs**; when `app` reports the healthcheck passing, open the domain
and log in.

---

## Operations

**Console** (Dokploy service → *Terminal*, `app` container):

```bash
php artisan about
php artisan queuebo:install --force        # re-run install
php artisan migrate --force
php artisan tinker
```

**Persistence** — one named volume managed by Dokploy, kept across redeploys:

| Volume    | Contents                                          |
|-----------|--------------------------------------------------|
| `storage` | `storage/` — uploads, logs, sessions, file cache |

The database and Redis are separate native services — back them up with
Dokploy's **native Database Backups** (per DB: S3 target + cron schedule +
restore) on each. To use a Dokploy-managed volume bind mount for `storage`
instead of the named volume, add it under the service's **Volumes** /
**Advanced → Mounts** and drop the matching `volumes:` entry.

**Config changes** — `OPTIMIZE=true` caches config/views and
`opcache.validate_timestamps=0`, so any env or code change needs a
**Redeploy**, not just a restart.

**Redis-backed queue** — set `QUEUE_CONNECTION=redis` (and optionally
`CACHE_STORE=redis`, `SESSION_DRIVER=redis`) in the Environment tab; it uses
the native Redis at `REDIS_HOST`.

**Auto-deploy on push** — enable the GitHub/GitLab webhook in the service's
**Deployments** tab.

---

## Troubleshooting

| Symptom | Check |
|---|---|
| 404 / 502 from Traefik | `app` on `dokploy-network`? healthcheck green? `APP_DOMAIN` matches the DNS record and the `Host()` rule? |
| Cert not issued | DNS resolves to this server; ports 80/443 open; Traefik logs for the ACME challenge. |
| Redirect loop / mixed content | `APP_URL` is `https://…` and `SESSION_SECURE_COOKIE=true`. The entrypoint pins `HTTPS on` for FastCGI from `APP_URL`. |
| `queue` / `scheduler` stuck "waiting for schema" | the `app` install step failed — read its log; fix, then Redeploy. |
| `app` stuck "waiting for DB" | `DB_HOST` is the native MySQL's **Internal Host** (not `localhost`/`mysql`)? that DB deployed and on `dokploy-network`? `DB_USERNAME`/`DB_PASSWORD` valid and `DB_DATABASE` created? |
| Login session drops after redeploy | `APP_KEY` must be a fixed value in the Environment tab, not regenerated per build. |
| Purchase-code prompt in installer | `INSTALLER_PURCHASE_CODE_REQUIRED=false` (headless `AUTO_INSTALL` needs no code). |
