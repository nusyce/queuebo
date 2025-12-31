# Queuebo on Dokploy

Deploy the full stack (`app` + `queue` + `scheduler` + `mysql` + `redis`)
as a single **Compose** service in [Dokploy](https://dokploy.com). Dokploy's
bundled Traefik handles the domain and TLS; everything else runs from
[`docker-compose.dokploy.yml`](../docker-compose.dokploy.yml).

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
| `DB_PASSWORD`       | app database user password.                                  |
| `DB_ROOT_PASSWORD`  | MySQL root password (used by the healthcheck).               |
| `ADMIN_EMAIL`       | first admin account, created on first boot.                  |

Leave `ADMIN_PASSWORD` blank to have a strong one generated and printed to
the `app` container log.

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
2. `mysql` comes up, healthcheck passes;
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

**Persistence** — named volumes managed by Dokploy, kept across redeploys:

| Volume    | Contents                                          |
|-----------|--------------------------------------------------|
| `mysql`   | database                                          |
| `redis`   | AOF file                                          |
| `storage` | `storage/` — uploads, logs, sessions, file cache |

Back these up with Dokploy's **Backups** (point it at the `mysql` service)
or a scheduled `mysqldump` from the console. To use Dokploy-managed volume
bind mounts instead of named volumes, add them under the service's
**Volumes** / **Advanced → Mounts** and drop the matching `volumes:` entry.

**Config changes** — `OPTIMIZE=true` caches config/views and
`opcache.validate_timestamps=0`, so any env or code change needs a
**Redeploy**, not just a restart.

**Redis-backed queue** — set `QUEUE_CONNECTION=redis` (and optionally
`CACHE_STORE=redis`, `SESSION_DRIVER=redis`) in the Environment tab; the
`redis` service is already running.

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
| Login session drops after redeploy | `APP_KEY` must be a fixed value in the Environment tab, not regenerated per build. |
| Purchase-code prompt in installer | `INSTALLER_PURCHASE_CODE_REQUIRED=false` (headless `AUTO_INSTALL` needs no code). |
