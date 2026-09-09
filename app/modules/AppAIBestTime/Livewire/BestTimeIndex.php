<?php

namespace Modules\AppAIBestTime\Livewire;

use Illuminate\Contracts\View\View;
use Throwable;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAIStudio\Support\AIStudioAccess;
use Modules\AppAIStudio\Support\AiContentStudioService;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

#[Title('AI Best Time')]
class BestTimeIndex extends Component
{
    use AIStudioAccess;

    public array $selectedAccountIds = [];
    public array $result = [];

    public function suggest(AiContentStudioService $studio): void
    {
        abort_unless($this->aiStudioFeatureEnabled('ai_studio_best_time'), 404);

        $accountIds = collect($this->selectedAccountIds)->map(fn ($id) => (int) $id)->filter()->values()->all();
        try {
            $planOwner = $this->aiStudioPlanOwner();

            if (function_exists('credit_service')) {
                credit_service()->ensureCanConsume($planOwner, 'ai_studio_best_time');
            }

            $this->result = $studio->bestTimeSuggestions($this->workspaceOwnerUserId(), $accountIds, $this->currentTimezone());

            if (function_exists('consume_credits')) {
                consume_credits($planOwner, 'ai_studio_best_time', [
                    'feature' => 'ai-studio.best-time',
                    'metadata' => ['account_ids' => $accountIds],
                ]);
            }
        } catch (Throwable $exception) {
            $this->addError('selectedAccountIds', $exception->getMessage());
        }
    }

    public function render(): View
    {
        abort_unless($this->aiStudioFeatureEnabled('ai_studio_best_time'), 404);

        $accounts = TeamWorkspaceAccess::accessibleAccountsQuery(auth()->user())
            ->where('created_by_user_id', $this->workspaceOwnerUserId())
            ->where('is_active', true)
            ->orderBy('display_name')
            ->get(['id', 'display_name', 'provider_key']);

        return view('appaibesttime::index', [
            'accounts' => $accounts,
            'creditPreview' => $this->aiStudioCreditPreview('ai_studio_best_time'),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('AI Best Time'),
        ]);
    }
}
