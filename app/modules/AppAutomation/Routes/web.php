<?php

use Illuminate\Support\Facades\Route;
use Modules\AppAutomation\Livewire\AutomationIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.appautomation.route_prefix', 'portal/automation'))
    ->name('portal.automation.')
    ->group(function (): void {
        Route::livewire('/', AutomationIndex::class)->name('index');
    });
