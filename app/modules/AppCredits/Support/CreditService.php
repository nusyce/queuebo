<?php

namespace Modules\AppCredits\Support;

use Carbon\CarbonInterface;
use Modules\AdminUser\Models\User;
use Modules\AppCredits\Models\CreditUsageLog;

class CreditService
{
    public function __construct(
        protected CreditSettings $settings,
        protected CreditTopupService $topups,
    ) {}

    public function summary(?User $user): array
    {
        if (! $user) {
            return [
                'limit' => null,
                'used' => 0,
                'remaining' => null,
                'unlimited' => true,
                'started_at' => null,
                'expires_at' => null,
                'plan_limit' => null,
                'plan_used' => 0,
                'plan_remaining' => null,
                'topup_remaining' => 0,
                'total_remaining' => null,
                'low_balance' => false,
            ];
        }

        $limit = $this->planLimit($user);
        $startedAt = $this->startedAt($user);
        $used = $this->used($user);
        $unlimited = ! $user->plan || $limit === -1;
        $topupRemaining = $unlimited ? 0 : $this->topups->remaining($user);
        $planUsed = $unlimited ? 0 : $this->planUsed($user, $used);
        $planRemaining = $unlimited ? null : max(0, $limit - $planUsed);
        $totalRemaining = $unlimited ? null : ($planRemaining + $topupRemaining);

        return [
            'limit' => $unlimited ? null : max(0, $limit),
            'used' => $used,
            'remaining' => $totalRemaining,
            'unlimited' => $unlimited,
            'started_at' => $startedAt,
            'expires_at' => $user->plan_expires_at,
            'plan_limit' => $unlimited ? null : max(0, $limit),
            'plan_used' => $planUsed,
            'plan_remaining' => $planRemaining,
            'topup_remaining' => $topupRemaining,
            'total_remaining' => $totalRemaining,
            'low_balance' => ! $unlimited && $totalRemaining !== null && $totalRemaining <= $this->settings->lowBalanceThreshold(),
        ];
    }

    public function planLimit(?User $user, int $default = -1): int
    {
        if (! $user) {
            return $default;
        }

        $value = $user->planLimit('credits_usage_limit', $default);
        return is_numeric($value) ? (int) round((float) $value) : $default;
    }

    public function used(?User $user): int
    {
        if (! $user) {
            return 0;
        }

        return (int) CreditUsageLog::query()
            ->where('user_id', $user->id)
            ->when($user->plan_id, fn ($query) => $query->where('plan_id', $user->plan_id))
            ->when($this->startedAt($user), fn ($query, $startedAt) => $query->where('created_at', '>=', $startedAt))
            ->sum('amount');
    }

    public function canConsume(?User $user, string $actionKey, int $quantity = 1, ?int $unitCost = null): bool
    {
        $quantity = max(1, $quantity);
        $summary = $this->summary($user);

        if ($summary['unlimited']) {
            return true;
        }

        $cost = ($unitCost ?? $this->costFor($user, $actionKey)) * $quantity;

        if ($this->settings->negativeBalanceAllowed() || ! $this->settings->actionBlockOnEmpty()) {
            return true;
        }

        return (int) ($summary['remaining'] ?? 0) >= $cost;
    }

    public function ensureCanConsume(?User $user, string $actionKey, int $quantity = 1, ?int $unitCost = null): void
    {
        if (! $this->canConsume($user, $actionKey, $quantity, $unitCost)) {
            throw new InsufficientCreditsException($this->settings->actionEmptyMessage());
        }
    }

    public function costFor(?User $user, string $actionKey, ?int $default = null): int
    {
        $definition = credit_action_registry()->get($actionKey);
        $planKey = $definition['plan_key'] ?? credit_action_registry()->planKeyFor($actionKey);
        $fallback = $default ?? (int) ($definition['default_cost'] ?? 0);

        if (! $user) {
            return max(0, $fallback);
        }

        $value = $user->planLimit($planKey, $fallback);
        return max(0, is_numeric($value) ? (int) round((float) $value) : $fallback);
    }

    public function consume(?User $user, string $actionKey, array $attributes = []): CreditUsageLog
    {
        $quantity = max(1, (int) ($attributes['quantity'] ?? 1));
        $unitCost = max(0, (int) ($attributes['unit_cost'] ?? $this->costFor($user, $actionKey)));
        $amount = max(0, (int) ($attributes['amount'] ?? ($unitCost * $quantity)));
        $summary = $this->summary($user);

        if (
            ! $summary['unlimited']
            && $this->settings->actionBlockOnEmpty()
            && ! $this->settings->negativeBalanceAllowed()
            && $amount > (int) ($summary['remaining'] ?? 0)
        ) {
            throw new InsufficientCreditsException($this->settings->actionEmptyMessage());
        }

        $before = $summary['unlimited'] ? null : (int) ($summary['remaining'] ?? 0);
        $after = $summary['unlimited'] ? null : ((int) ($summary['remaining'] ?? 0) - $amount);

        if (! $summary['unlimited'] && $user) {
            $topupRemaining = (int) ($summary['topup_remaining'] ?? 0);
            $planRemaining = (int) ($summary['plan_remaining'] ?? 0);
            $topupSpend = $this->settings->spendPriority() === 'topup_first'
                ? min($amount, $topupRemaining)
                : min(max(0, $amount - $planRemaining), $topupRemaining);

            if ($topupSpend > 0) {
                $this->topups->consume($user, $topupSpend, [
                    'action_key' => $actionKey,
                    'feature' => $attributes['feature'] ?? null,
                    'quantity' => $attributes['quantity'] ?? 1,
                    'metadata' => $attributes['metadata'] ?? [],
                ]);
            }
        }

        return CreditUsageLog::query()->create([
            'user_id' => $user?->id,
            'plan_id' => $user?->plan_id,
            'action_key' => $actionKey,
            'feature' => $attributes['feature'] ?? null,
            'amount' => $amount,
            'unit_cost' => $unitCost,
            'quantity' => $quantity,
            'credits_before' => $before,
            'credits_after' => $summary['unlimited'] ? null : max(0, $after),
            'is_unlimited' => (bool) $summary['unlimited'],
            'metadata' => $attributes['metadata'] ?? [],
        ]);
    }

    protected function planUsed(User $user, ?int $used = null): int
    {
        $used ??= $this->used($user);

        if ($this->settings->spendPriority() === 'topup_first') {
            return max(0, $used - $this->topups->totalConsumed($user));
        }

        return min($this->planLimit($user), $used);
    }

    protected function startedAt(User $user): ?CarbonInterface
    {
        return $user->plan_started_at ?: $user->created_at;
    }
}
