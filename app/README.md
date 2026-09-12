# `app/` — Queuebo application source

Queuebo is built, Laravel 13 /
Livewire 4 / PHP 8.3, **rebranded to Queuebo** (see `../docs/REBRAND.md`).
The tree is committed with `vendor/` and `public/build/` included so the image
builds with no Composer/npm step.

Changes relative to the stock `Install.zip`:
- `../docs/CLEANUP.md` — dev artefacts, local runtime state, VCS hygiene
- `../docs/REBRAND.md` — Stackposts → Queuebo string/identifier changes

No application logic was changed by either pass.


## Refreshing to a new upstream release

```bash
rm -rf app                       # keep this README + docs/ handy
unzip Install.zip -d app/
# re-apply docs/CLEANUP.md + docs/REBRAND.md, then:
git add app && git commit -m "vendor: upstream vX.Y + rebrand"
```
