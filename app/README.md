# `app/` — Stackposts application source

**This directory is intentionally empty in version control.** Place the
Stackposts v10 application here — the contents of `Install.zip` from **your
own CodeCanyon / Envato purchase** (item `21747459`).

After copying, `app/` must contain at least:

```
app/artisan
app/composer.json
app/public/index.php
app/app/  app/modules/  app/config/  app/routes/  app/vendor/  ...
```

## Populate it

```bash
# from your licensed download
unzip /path/to/Install.zip -d app/

# sanity check
test -f app/artisan && test -f app/composer.json && echo OK
```

Then from the repo root:

```bash
cp .env.docker.example .env.docker   # edit it
docker compose --env-file .env.docker up -d --build
# open http://localhost:8080/installer
```

## Do not

- Do not commit a "nulled" / cracked build. It violates the Envato licence
  and those packages routinely carry injected backdoors.
- Do not commit `app/.env`, `app/auth.json`, `app/storage/*.key`,
  `app/Install.zip` or the `NullPHPscript.com.html` / `Download More PHP
  Scripts.html` marker files — `.gitignore` already blocks them.
- Keep this repository **private**.
