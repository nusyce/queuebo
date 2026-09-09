<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminSystemInformation\Livewire\SystemInformationIndex;

Route::middleware(['auth'])->group(function () {
    Route::livewire('settings/system-information', SystemInformationIndex::class);
});

Route::prefix('admin/settings')->group(function () {
    Route::middleware(['auth'])->group(function () {
        Route::livewire('system-information', SystemInformationIndex::class)->name('admin-system-information.index');
    });
});
