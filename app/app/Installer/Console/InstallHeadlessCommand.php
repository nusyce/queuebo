<?php

namespace App\Installer\Console;

use App\Installer\Support\InstallerService;
use App\Installer\Support\InstallerState;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Throwable;

class InstallHeadlessCommand extends Command
{
    protected $signature = 'queuebo:install
        {--force : Re-run provisioning even if the app already looks installed}
        {--title= : Site title (default: APP_NAME)}
        {--description= : Site meta description (default: SITE_DESCRIPTION)}
        {--keywords= : Site meta keywords (default: SITE_KEYWORDS)}
        {--admin-name= : Administrator display name (default: ADMIN_NAME)}
        {--admin-email= : Administrator email (default: ADMIN_EMAIL)}
        {--admin-username= : Administrator username (default: ADMIN_USERNAME)}
        {--admin-password= : Administrator password (default: ADMIN_PASSWORD, else generated)}
        {--timezone= : Administrator / app timezone (default: APP_TIMEZONE)}';

    protected $description = 'Provision Queuebo against a pre-configured database (migrations, seeders, admin user) without the web installer.';

    protected bool $generatedPassword = false;

    public function handle(InstallerState $state, InstallerService $installer): int
    {
        if ($state->isInstalled() && ! $this->option('force')) {
            $this->info('Queuebo already appears to be installed. Pass --force to re-run provisioning.');

            return self::SUCCESS;
        }

        $timezone = $this->resolveTimezone();
        $password = $this->resolvePassword();
        $generatedPassword = $this->generatedPassword;

        $data = [
            'website_title' => $this->value('title', env('APP_NAME'), 'Queuebo'),
            'website_description' => $this->value('description', env('SITE_DESCRIPTION'), ''),
            'website_keywords' => $this->value('keywords', env('SITE_KEYWORDS'), ''),
            'admin_name' => $this->value('admin-name', env('ADMIN_NAME'), 'Administrator'),
            'admin_email' => strtolower($this->value('admin-email', env('ADMIN_EMAIL'), 'admin@example.com')),
            'admin_username' => strtolower($this->value('admin-username', env('ADMIN_USERNAME'), 'admin')),
            'admin_password' => $password,
            'admin_timezone' => $timezone,
        ];

        $this->line("  Site title ....... {$data['website_title']}");
        $this->line("  Admin email ...... {$data['admin_email']}");
        $this->line("  Admin username ... {$data['admin_username']}");
        $this->line("  Timezone ......... {$data['admin_timezone']}");

        if (mb_strlen($password) < 8) {
            $this->warn('  Administrator password is shorter than 8 characters.');
        }

        try {
            $this->info('Running migrations, seeders and administrator provisioning...');
            $user = $installer->installHeadless($data);
        } catch (Throwable $e) {
            $this->error('Headless install failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Queuebo is installed.');
        $this->line("  Login ............ {$user->email}");

        if ($generatedPassword) {
            $this->warn("  Password ......... {$password}  (generated — store it now)");
        }

        return self::SUCCESS;
    }

    protected function value(string $option, mixed $env, string $fallback): string
    {
        $opt = $this->option($option);

        if (is_string($opt) && $opt !== '') {
            return $opt;
        }

        $env = is_string($env) ? trim($env) : '';

        return $env !== '' ? $env : $fallback;
    }

    protected function resolvePassword(): string
    {
        $explicit = (string) ($this->option('admin-password') ?? '');

        if ($explicit !== '') {
            return $explicit;
        }

        $fromEnv = trim((string) env('ADMIN_PASSWORD'));

        if ($fromEnv !== '') {
            return $fromEnv;
        }

        if ($this->input->isInteractive() && ! $this->option('no-interaction')) {
            $typed = (string) $this->secret('Administrator password (min 8 chars, leave blank to generate)');

            if (trim($typed) !== '') {
                return $typed;
            }
        }

        $this->generatedPassword = true;

        return Str::password(16, symbols: false);
    }

    protected function resolveTimezone(): string
    {
        $candidate = (string) ($this->option('timezone') ?: env('APP_TIMEZONE') ?: config('app.timezone') ?: 'UTC');

        return in_array($candidate, timezone_options(), true) ? $candidate : 'UTC';
    }
}
