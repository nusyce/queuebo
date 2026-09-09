# `app/` — Queuebo application source

Queuebo is built on Stackposts v10 (CodeCanyon item `21747459`), Laravel 13 /
Livewire 4 / PHP 8.3, **rebranded to Queuebo** (see `../docs/REBRAND.md`).
The tree is committed with `vendor/` and `public/build/` included so the image
builds with no Composer/npm step.

Changes relative to the stock `Install.zip`:
- `../docs/CLEANUP.md` — dev artefacts, local runtime state, VCS hygiene
- `../docs/REBRAND.md` — Stackposts → Queuebo string/identifier changes

No application logic was changed by either pass.

## Notes

- The upstream base must come from your own CodeCanyon / Envato purchase.
- Real config is supplied at runtime via `../.env.docker` — `app/.env` is not
  committed (`.gitignore`).
- Keep this repository **private** (Envato commercial licence).
- `app/app/Installer/config/installer.php` still points `purchase_verify_url`
  at `stackposts.com` — that is the upstream licence server; leave it.

## Refreshing to a new upstream release

```bash
rm -rf app                       # keep this README + docs/ handy
unzip Install.zip -d app/
# re-apply docs/CLEANUP.md + docs/REBRAND.md, then:
git add app && git commit -m "vendor: upstream vX.Y + rebrand"
```
