<?php

namespace Modules\AppAIStudio\Support;

use Modules\AppAIStudio\Models\AIStudioUserSetting;
use Modules\AppAIStudio\Models\AIStudioWorkspaceSetting;
use Modules\AdminUser\Models\User;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

trait AIStudioAccess
{
    protected function aiStudioCreditPreview(string $actionKey, ?int $unitCost = null, int $quantity = 1): array
    {
        $planOwner = $this->aiStudioPlanOwner();
        $quantity = max(1, $quantity);
        $summary = function_exists('credit_summary')
            ? credit_summary($planOwner)
            : ['remaining' => null, 'unlimited' => true];

        $cost = function_exists('credit_service')
            ? max(0, (int) ($unitCost ?? credit_service()->costFor($planOwner, $actionKey)))
            : max(0, (int) ($unitCost ?? 0));

        $amount = max(0, $cost * $quantity);
        $remaining = $summary['remaining'] ?? null;
        $unlimited = (bool) ($summary['unlimited'] ?? true);

        return [
            'action_key' => $actionKey,
            'unit_cost' => $cost,
            'quantity' => $quantity,
            'amount' => $amount,
            'remaining' => $remaining,
            'unlimited' => $unlimited,
            'enough' => $unlimited || $remaining === null || (int) $remaining >= $amount,
        ];
    }

    protected function aiStudioEnabled(): bool
    {
        $planOwner = $this->aiStudioPlanOwner();

        if (! $this->aiStudioWorkspaceAvailable()) {
            return false;
        }

        return $planOwner?->canUsePlanFeature('ai_studio') ?? false;
    }

    protected function aiStudioFeatureEnabled(string $featureKey): bool
    {
        $planOwner = $this->aiStudioPlanOwner();

        if (! $this->aiStudioEnabled()) {
            return false;
        }

        return $planOwner?->canUsePlanFeature($featureKey) ?? false;
    }

    protected function aiStudioPlanOwner(): ?User
    {
        $user = auth()->user();
        $team = TeamWorkspaceAccess::activeTeam($user);

        return $team?->owner ?: $user;
    }

    protected function aiStudioWorkspaceAvailable(): bool
    {
        return TeamWorkspaceAccess::teamHasModule(TeamWorkspaceAccess::activeTeam(auth()->user()), 'publishing');
    }

    protected function workspaceOwnerUserId(): int
    {
        return TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());
    }

    protected function currentTimezone(): string
    {
        return (string) (auth()->user()?->timezone ?: config('app.timezone'));
    }

    protected function aiStudioUserSettings(): array
    {
        $userId = (int) auth()->id();

        if ($userId <= 0) {
            return [];
        }

        return (array) (AIStudioUserSetting::query()->forUser($userId)->value('settings') ?? []);
    }

    protected function aiStudioWorkspaceSettings(): array
    {
        $user = auth()->user();
        $ownerUserId = $this->workspaceOwnerUserId();

        if ($ownerUserId <= 0) {
            return [];
        }

        $teamId = TeamWorkspaceAccess::activeTeam($user)?->id;

        return (array) AIStudioWorkspaceSetting::query()
            ->ownedBy($ownerUserId)
            ->forTeam($teamId)
            ->value('settings') ?? [];
    }

    protected function aiStudioSetting(string $key, mixed $default = null): mixed
    {
        $workspace = $this->aiStudioWorkspaceSettings();
        $user = $this->aiStudioUserSettings();

        if (array_key_exists($key, $user) && $user[$key] !== null && $user[$key] !== '') {
            return $user[$key];
        }

        if (array_key_exists($key, $workspace) && $workspace[$key] !== null && $workspace[$key] !== '') {
            return $workspace[$key];
        }

        return $default;
    }

    protected function aiStudioWorkspacePromptConfig(): array
    {
        return array_filter([
            'brand_voice' => $this->aiStudioSetting('brand_voice'),
            'preferred_cta_style' => $this->aiStudioSetting('preferred_cta_style'),
            'banned_words' => $this->aiStudioSetting('banned_words'),
        ], fn ($value) => filled($value));
    }
}
