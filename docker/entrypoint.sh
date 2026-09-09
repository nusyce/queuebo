#!/usr/bin/env bash
# Stackposts container entrypoint.
# Role is selected with CONTAINER_ROLE: app (default) | queue | scheduler.
set -euo pipefail

ROLE="${CONTAINER_ROLE:-app}"
ARTISAN="php /var/www/html/artisan"

log() { printf '[entrypoint] %s\n' "$*"; }

cd /var/www/html

# --- .env bootstrap -------------------------------------------------------
if [ ! -f .env ]; then
    if [ -f .env.docker ]; then
        log ".env missing — copying .env.docker"
        cp .env.docker .env
    elif [ -f .env.example ]; then
        log ".env missing — copying .env.example"
        cp .env.example .env
    fi
fi

# --- Dependencies (only if a source tree without vendor/ was mounted) ----
if [ ! -f vendor/autoload.php ]; then
    log "vendor/ not found — running composer install --no-dev"
    composer install --no-dev --no-interaction --prefer-dist \
        --optimize-autoloader --no-progress
fi

# --- Wait for the database ---------------------------------------------
if [ "${WAIT_FOR_DB:-true}" = "true" ]; then
    log "waiting for database ${DB_HOST:-mysql}:${DB_PORT:-3306}"
    php -r '
        $host=getenv("DB_HOST")?:"mysql";
        $port=getenv("DB_PORT")?:"3306";
        $db=getenv("DB_DATABASE")?:"stackposts";
        $user=getenv("DB_USERNAME")?:"stackposts";
        $pass=getenv("DB_PASSWORD")?:"";
        for ($i=0; $i<60; $i++) {
            try { new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass,
                [PDO::ATTR_TIMEOUT=>2]); exit(0); }
            catch (Throwable $e) { fwrite(STDERR, "  db not ready: ".$e->getMessage()."\n"); sleep(2); }
        }
        fwrite(STDERR, "database never became reachable\n"); exit(1);
    '
fi

# --- Workers wait until the app schema exists ------------------------
# queue:work / schedule:work hit the database (database queue + cache stores)
# on the first tick. Before Stackposts is installed there are no tables, so
# hold here until the installer (or `migrate`) has created them.
if [ "$ROLE" = "queue" ] || [ "$ROLE" = "scheduler" ]; then
    if [ "${WAIT_FOR_INSTALL:-true}" = "true" ]; then
        log "waiting for the Stackposts schema (run the installer if this hangs)"
        until php -r '
            try {
                $p = new PDO(
                    "mysql:host=".(getenv("DB_HOST")?:"mysql").";port=".(getenv("DB_PORT")?:"3306").";dbname=".(getenv("DB_DATABASE")?:"stackposts"),
                    getenv("DB_USERNAME")?:"stackposts", getenv("DB_PASSWORD")?:"", [PDO::ATTR_TIMEOUT=>2]);
                exit($p->query("SHOW TABLES LIKE \"migrations\"")->fetch() ? 0 : 1);
            } catch (Throwable $e) { exit(1); }
        '; do sleep 5; done
        log "schema present — continuing"
    fi
fi

# --- One-time app prep — only the app role does this ------------------
if [ "$ROLE" = "app" ]; then
    if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
        log "generating APP_KEY"
        $ARTISAN key:generate --force
    fi

    if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
        log "running migrations"
        $ARTISAN migrate --force
    fi

    log "linking public storage"
    $ARTISAN storage:link --force || true

    if [ "${OPTIMIZE:-false}" = "true" ]; then
        log "caching config / views / events"
        $ARTISAN config:cache
        $ARTISAN view:cache
        $ARTISAN event:cache || true
        # NOTE: no route:cache — routes/web.php defines closure routes which
        #       Laravel cannot serialise.
    else
        log "clearing stale file caches (OPTIMIZE=false)"
        $ARTISAN config:clear || true
        $ARTISAN view:clear   || true
        $ARTISAN event:clear  || true
        rm -f bootstrap/cache/routes-v7.php bootstrap/cache/config.php 2>/dev/null || true
    fi
fi

# Keep runtime dirs writable when storage/ is a fresh named volume.
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# --- Hand off to the role's process ----------------------------------
case "$ROLE" in
    app)
        log "starting web (nginx + php-fpm)"
        exec "$@"
        ;;
    queue)
        log "starting queue worker"
        exec runuser -u www-data -- php artisan queue:work \
            --sleep=3 --tries=3 --max-time=3600 --timeout=120
        ;;
    scheduler)
        log "starting scheduler loop"
        exec runuser -u www-data -- php artisan schedule:work
        ;;
    *)
        log "unknown CONTAINER_ROLE='$ROLE'"
        exit 1
        ;;
esac
