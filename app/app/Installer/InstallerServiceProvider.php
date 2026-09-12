<?php

namespace App\Installer;

use App\Installer\Console\InstallHeadlessCommand;
use Illuminate\Support\ServiceProvider;

class InstallerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/installer.php', 'installer');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'installer');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallHeadlessCommand::class,
            ]);
        }
    }
}
