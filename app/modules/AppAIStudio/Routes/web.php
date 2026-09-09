<?php

use Illuminate\Support\Facades\Route;
use Modules\AppAIStudio\Http\Controllers\AISettingsController;
use Modules\AppAIStudio\Http\Controllers\PromptHistoryController;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.appaistudio.route_prefix', 'portal/ai-studio'))
    ->group(function (): void {
        Route::get('/', function () {
            $user = auth()->user();
            $team = TeamWorkspaceAccess::activeTeam($user);
            $planOwner = $team?->owner ?: $user;
            $enabled = fn (string $feature): bool => ! $planOwner?->plan || $planOwner->hasPlanFeature($feature);

            $route = match (true) {
                $enabled('ai_studio_caption_generator') => 'portal.ai-content',
                $enabled('ai_studio_repurpose') => 'portal.ai-repurpose',
                $enabled('ai_studio_content_planner') => 'portal.ai-content-planner',
                $enabled('ai_studio_best_time') => 'portal.ai-best-time',
                $enabled('ai_studio_image') => 'portal.ai-image',
                $enabled('ai_studio_video') => 'portal.ai-video',
                $enabled('ai_studio_review') => 'portal.ai-review',
                $enabled('ai_studio_semantic_search') => 'portal.ai-semantic-search',
                default => 'portal.ai-content',
            };

            return redirect()->route($route);
        })->name('portal.ai-studio');

        Route::get('/prompt-history', [PromptHistoryController::class, 'index'])->name('portal.ai-studio.prompt-history');
        Route::put('/prompt-history/{historyId}', [PromptHistoryController::class, 'update'])->name('portal.ai-studio.prompt-history.update');
        Route::delete('/prompt-history/{historyId}', [PromptHistoryController::class, 'destroy'])->name('portal.ai-studio.prompt-history.destroy');
        Route::get('/settings', [AISettingsController::class, 'index'])->name('portal.ai-studio.settings');
        Route::put('/settings/user', [AISettingsController::class, 'updateUser'])->name('portal.ai-studio.settings.user');
        Route::put('/settings/workspace', [AISettingsController::class, 'updateWorkspace'])->name('portal.ai-studio.settings.workspace');
    });
