# Stackposts v10.0 — analyse du code

## Contenu de l'archive

`Stackposts v10.0.rar` (56 Mo) →
`codecanyon-21747459-stackposts-social-marketing-tool/Install.zip`
→ l'application (16 717 fichiers, ~150 Mo décompressés).

Stackposts est vendu sous **licence commerciale Envato** (item CodeCanyon
`21747459`). Le conteneur ci-joint est prévu pour être appliqué à une copie
issue de **votre propre achat CodeCanyon / Envato**, déployée dans un dépôt
**privé**.

## Pile technique

| Élément | Détail |
|---|---|
| Framework | Laravel `^13.0`, Livewire `^4.1`, Fortify (auth), PHP `^8.3` |
| Base « starter » | `laravel/livewire-starter-kit` (composer.json) |
| Architecture | monolithe modulaire — **79 modules** sous `modules/` (PSR-4 `Modules\`), 859 fichiers PHP, 526 vues Blade |
| Front-end | Vite (build **pré-compilé** livré dans `public/build/`), thèmes Tailwind/FontAwesome sous `resources/themes/`, Highcharts |
| Base de données | MySQL ; migrations réparties dans chaque module |
| File / cache / session | pilotes `database` en production, `file` pour l'installateur |
| PDF | `barryvdh/laravel-dompdf` |
| IA | `laravel/ai`, `stichoza/google-translate-php`, modules `AppAI*` (contenu, image, vidéo, repurpose, recherche sémantique, meilleur horaire) |
| Stockage | `league/flysystem-aws-s3-v3`, `aws/aws-sdk-php` |
| Paiement | `stripe/stripe-php` + **14 modules passerelles** : Stripe, PayPal, Razorpay, Paystack, Flutterwave, PayU, 2Checkout, CCAvenue, Iyzico, Paytm, Instamojo, SslCommerz, YooMoney, PayTR |
| Réseaux sociaux | modules `AppChannel*` : Facebook Pages, Instagram (officiel + non officiel), LinkedIn (pages + profils), TikTok, X |
| Dev / tests | Pest 4, Pint, Sail, Pail, Collision, Mockery, Faker |

## Structure

```
app/            Controllers, Livewire (Auth, Portal), Providers, Support, Notifications
app/Installer/  assistant web d'installation (vérif. code d'achat + config DB + admin)
modules/        79 modules Admin*/App*/Payment*  (chacun : Http, Resources/views, migrations, routes)
routes/         web.php, settings.php, console.php, public-storage.php
config/ database/ lang/ resources/ storage/ public/ vendor/(livré)
```

### Assistant d'installation (`app/Installer/`)

* Routes `/installer` (GET/POST), middleware `RedirectIfInstalled`.
* `InstallerController::rules()` : `purchase_code` requis
  (`config('installer.purchase_code_required') === true`).
* `InstallerService::verifyPurchaseCode()` appelle
  `https://stackposts.com/api/marketplace/install` puis stocke en base
  `license_purchase_code`, `license_status`, `license_product_id`,
  `license_verified_at`, `license_meta`.
* En fin d'installation : exécute les migrations, crée le compte admin
  (plan `agency-lifetime` par défaut), passe `APP_INSTALLED=true`.
* → l'hôte de déploiement doit avoir une **sortie HTTPS** vers
  `stackposts.com` et vers les API des réseaux/paiements.

### Points d'entrée

* `public/index.php` — bootstrap Laravel standard (`bootstrap/app.php`,
  `$app->handleRequest()`).
* `index.php` racine — résidu (non utilisé quand le docroot = `public/`).
* `.htaccess` — règles Apache mod_rewrite (bloque `.env`, retire le slash
  final, route vers `index.php`). Le conteneur utilise nginx à la place.

### Extensions PHP requises (composer.lock)

Noyau : `ctype fileinfo filter hash json mbstring openssl pdo tokenizer xml`
Application : `pdo_mysql curl bcmath gd zip intl exif pcntl` + `redis`,
`imagick`/`gd` pour le traitement d'images. Optionnelles selon config :
`memcached`, `mongodb`, `amqp`, `apcu`.

## Observations

* Paquet « prêt à déployer » : `vendor/` et `public/build/` sont fournis →
  aucun `composer install` / `npm run build` nécessaire pour démarrer.
* `.env.example` : `APP_ENV=production`, `APP_DEBUG=false`,
  `APP_INSTALLED=false`, pilotes `database`.
* `.env` inclus dans l'archive : contient une `APP_KEY` de démo et des
  identifiants DB vides — **à régénérer / ne pas réutiliser**.
* Seulement 4 migrations à la racine ; le schéma vit dans les modules.
* `routes/web.php` définit des routes par closure (`/`) → `route:cache`
  impossible (le conteneur met en cache config/vues/événements seulement).
