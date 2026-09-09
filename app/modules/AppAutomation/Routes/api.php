<?php

use Illuminate\Support\Facades\Route;
use Modules\AppAutomation\Http\Controllers\AutomationApiController;

Route::middleware(['api'])
    ->prefix(config('modules.appautomation.api_prefix', 'api/automation/v1'))
    ->name('api.automation.')
    ->group(function (): void {
        Route::get('/me', [AutomationApiController::class, 'me'])->name('me');
        Route::get('/accounts', [AutomationApiController::class, 'accounts'])->name('accounts');
        Route::post('/posts', [AutomationApiController::class, 'createPost'])->name('posts.store');
    });
