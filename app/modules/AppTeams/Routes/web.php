<?php

use Illuminate\Support\Facades\Route;
use Modules\AppTeams\Http\Controllers\SwitchWorkspaceController;
use Modules\AppTeams\Http\Controllers\TeamConversationStreamController;
use Modules\AppTeams\Livewire\Workspace;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.appteams.route_prefix', 'portal/teams'))
    ->group(function (): void {
        Route::get('/stream', TeamConversationStreamController::class)->name('portal.teams.stream');
        Route::get('/switch', SwitchWorkspaceController::class)->name('portal.teams.switch');
        Route::livewire('/', Workspace::class)->name('portal.teams');
        Route::livewire('/join/{inviteCode}', Workspace::class)->name('portal.teams.join');
    });
