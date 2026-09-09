# `app/` — Stackposts application source

Stackposts v10 (CodeCanyon item `21747459`), Laravel 13 / Livewire 4 / PHP 8.3.
The tree is committed with `vendor/` and `public/build/` included so the image
builds with no Composer/npm step.

What was changed relative to the stock `Install.zip` is listed in
[`../docs/CLEANUP.md`](../docs/CLEANUP.md) — dev artefacts, local runtime state
and VCS hygiene only; no application logic touched.

## Notes

- Use a copy from your own CodeCanyon / Envato purchase; diff it against
  `docs/CLEANUP.md` to confirm the delta.
- Real config is supplied at runtime via `../.env.docker` — `app/.env` is not
  committed (`.gitignore`).
- Keep this repository **private** (Envato commercial licence).

## Refreshing to a new Stackposts release

```bash
rm -rf app                       # keep this README + docs/CLEANUP.md handy
unzip Install.zip -d app/
# re-apply the CLEANUP.md steps, then:
git add app && git commit -m "vendor: Stackposts vX.Y"
```
