<?php

namespace Modules\AppAffiliate\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\AdminPaymentHistory\Models\PaymentHistory;
use Modules\AdminSettings\Support\OptionStore;
use Modules\AdminUser\Models\User;
use Modules\AppAffiliate\Models\AffiliateCommission;
use Modules\AppAffiliate\Models\AffiliateProfile;
use Modules\AppAffiliate\Models\AffiliateWithdrawal;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

class AffiliateService
{
    public function __construct(
        protected OptionStore $options,
    ) {}

    public function isEnabled(): bool
    {
        return (string) $this->options->get('affiliate_status', '1') === '1';
    }

    public function userCanAccess(?User $user): bool
    {
        if (! $this->isEnabled() || ! $user) {
            return false;
        }

        $team = TeamWorkspaceAccess::activeTeam($user);
        $planOwner = $team?->owner ?: $user;

        if (! $planOwner?->plan) {
            return true;
        }

        $permissions = $planOwner->plan->permissions;
        $permissions = is_array($permissions) ? $permissions : [];
        $hasAffiliatePermission = array_key_exists('affiliate', $permissions) || array_key_exists('appaffiliate', $permissions);

        if (! $hasAffiliatePermission) {
            return true;
        }

        return $planOwner->hasPlanFeature('affiliate')
            || $planOwner->hasPlanFeature('appaffiliate');
    }

    public function commissionPercentage(): float
    {
        return max(0, (float) $this->options->get('affiliate_commission_percentage', 15));
    }

    public function minimumWithdrawal(): float
    {
        return max(0, (float) $this->options->get('affiliate_minimum_withdrawal', 50));
    }

    public function oneTimeCommission(): bool
    {
        return (string) $this->options->get('affiliate_onetime_commission_status', '1') === '1';
    }

    public function allowedPaymentSources(): array
    {
        return collect(preg_split('/[\r\n,]+/', (string) $this->options->get('affiliate_types_of_payments', 'manual')))
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->map(fn ($item) => strtolower($item))
            ->values()
            ->all();
    }

    public function ensureReferralCode(User $user): string
    {
        if (filled($user->referral_code)) {
            return (string) $user->referral_code;
        }

        $code = $this->generateReferralCode($user->username ?: $user->name ?: 'ref');

        $user->forceFill([
            'referral_code' => $code,
        ])->save();

        return $code;
    }

    public function ensureProfile(User $user): AffiliateProfile
    {
        return AffiliateProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'clicks' => 0,
                'conversions' => 0,
                'total_approved' => 0,
                'total_withdrawal' => 0,
                'total_balance' => 0,
            ],
        );
    }

    public function captureReferral(Request $request): void
    {
        if (! $this->isEnabled() || ! $request->has('ref')) {
            return;
        }

        $code = trim((string) $request->query('ref'));
        $affiliateUser = User::query()->where('referral_code', $code)->first();

        if (! $affiliateUser) {
            return;
        }

        if ($request->user() && (int) $request->user()->id === (int) $affiliateUser->id) {
            return;
        }

        session([
            'affiliate_referral_code' => $code,
            'affiliate_referrer_user_id' => $affiliateUser->id,
        ]);

        $cookieName = 'affiliate_ref_seen_'.$affiliateUser->id;

        if (! $request->hasCookie($cookieName)) {
            $profile = $this->ensureProfile($affiliateUser);
            $profile->increment('clicks');
            Cookie::queue($cookieName, '1', 60 * 24);
        }
    }

    public function referredByUserIdFromSession(): ?int
    {
        $code = trim((string) session('affiliate_referral_code', ''));

        if ($code === '') {
            return null;
        }

        return User::query()->where('referral_code', $code)->value('id');
    }

    public function referralLink(User $user): string
    {
        return url('/?ref='.$this->ensureReferralCode($user));
    }

    public function applyCommissionFromPaymentHistory(PaymentHistory $payment): ?AffiliateCommission
    {
        if (! $this->isEnabled() || (int) $payment->status !== 1) {
            return null;
        }

        $payment->loadMissing('user');
        $customer = $payment->user;

        if (! $customer || ! $customer->referred_by_user_id) {
            return null;
        }

        $affiliateUser = User::query()->find((int) $customer->referred_by_user_id);

        if (! $affiliateUser || (int) $affiliateUser->id === (int) $customer->id) {
            return null;
        }

        $allowedSources = $this->allowedPaymentSources();
        $paymentSource = strtolower(trim((string) $payment->from));

        if ($allowedSources !== [] && ! in_array($paymentSource, $allowedSources, true)) {
            return null;
        }

        if ($this->oneTimeCommission() && AffiliateCommission::query()->where('referred_user_id', $customer->id)->exists()) {
            return null;
        }

        $existing = AffiliateCommission::query()
            ->where('payment_history_id', $payment->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $rate = $this->commissionPercentage();
        $amount = (float) $payment->amount;
        $commissionValue = round($amount * $rate / 100, 2);

        if ($commissionValue <= 0) {
            return null;
        }

        $this->ensureProfile($affiliateUser);

        return DB::transaction(function () use ($affiliateUser, $customer, $payment, $amount, $rate, $commissionValue): AffiliateCommission {
            $profile = $this->ensureProfile($affiliateUser);
            $profile->increment('conversions');

            return AffiliateCommission::query()->create([
                'id_secure' => Str::random(32),
                'affiliate_user_id' => $affiliateUser->id,
                'referred_user_id' => $customer->id,
                'payment_history_id' => $payment->id,
                'amount' => $amount,
                'commission_rate' => $rate,
                'commission' => $commissionValue,
                'status' => AffiliateCommission::STATUS_PENDING,
                'meta' => [
                    'payment_id' => $payment->transaction_id,
                    'plan_id' => $payment->plan_id,
                    'source' => $payment->from,
                ],
            ]);
        });
    }

    public function approveCommission(AffiliateCommission $commission): void
    {
        if ((int) $commission->status === AffiliateCommission::STATUS_APPROVED) {
            return;
        }

        DB::transaction(function () use ($commission): void {
            $profile = $this->ensureProfile($commission->affiliateUser);

            if ((int) $commission->status !== AffiliateCommission::STATUS_APPROVED) {
                $profile->forceFill([
                    'total_approved' => (float) $profile->total_approved + (float) $commission->commission,
                    'total_balance' => (float) $profile->total_balance + (float) $commission->commission,
                ])->save();
            }

            $commission->forceFill([
                'status' => AffiliateCommission::STATUS_APPROVED,
                'approved_at' => now(),
                'rejected_at' => null,
            ])->save();
        });
    }

    public function rejectCommission(AffiliateCommission $commission): void
    {
        if ((int) $commission->status === AffiliateCommission::STATUS_REJECTED) {
            return;
        }

        DB::transaction(function () use ($commission): void {
            if ((int) $commission->status === AffiliateCommission::STATUS_APPROVED) {
                $profile = $this->ensureProfile($commission->affiliateUser);
                $profile->forceFill([
                    'total_approved' => max(0, (float) $profile->total_approved - (float) $commission->commission),
                    'total_balance' => max(0, (float) $profile->total_balance - (float) $commission->commission),
                ])->save();
            }

            $commission->forceFill([
                'status' => AffiliateCommission::STATUS_REJECTED,
                'rejected_at' => now(),
            ])->save();
        });
    }

    public function requestWithdrawal(User $user, float $amount, string $paymentMethod, ?string $paymentDetails = null): AffiliateWithdrawal
    {
        $profile = $this->ensureProfile($user);
        $minimum = $this->minimumWithdrawal();

        if ($amount < $minimum) {
            throw new \RuntimeException(__('The amount must be greater than or equal to :amount.', ['amount' => number_format($minimum, 2)]));
        }

        if ($amount > (float) $profile->total_balance) {
            throw new \RuntimeException(__('Insufficient affiliate balance.'));
        }

        return DB::transaction(function () use ($user, $profile, $amount, $paymentMethod, $paymentDetails): AffiliateWithdrawal {
            $profile->forceFill([
                'total_balance' => max(0, (float) $profile->total_balance - $amount),
            ])->save();

            return AffiliateWithdrawal::query()->create([
                'id_secure' => Str::random(32),
                'affiliate_user_id' => $user->id,
                'amount' => $amount,
                'payment_method' => trim($paymentMethod),
                'payment_details' => $paymentDetails ? trim($paymentDetails) : null,
                'status' => AffiliateWithdrawal::STATUS_PENDING,
            ]);
        });
    }

    public function approveWithdrawal(AffiliateWithdrawal $withdrawal, ?string $notes = null): void
    {
        if ((int) $withdrawal->status === AffiliateWithdrawal::STATUS_APPROVED) {
            return;
        }

        DB::transaction(function () use ($withdrawal, $notes): void {
            $profile = $this->ensureProfile($withdrawal->affiliateUser);

            if ((int) $withdrawal->status !== AffiliateWithdrawal::STATUS_APPROVED) {
                $profile->forceFill([
                    'total_withdrawal' => (float) $profile->total_withdrawal + (float) $withdrawal->amount,
                ])->save();
            }

            $withdrawal->forceFill([
                'status' => AffiliateWithdrawal::STATUS_APPROVED,
                'notes' => $notes ?: $withdrawal->notes,
                'processed_at' => now(),
            ])->save();
        });
    }

    public function rejectWithdrawal(AffiliateWithdrawal $withdrawal, ?string $notes = null): void
    {
        if ((int) $withdrawal->status === AffiliateWithdrawal::STATUS_REJECTED) {
            return;
        }

        DB::transaction(function () use ($withdrawal, $notes): void {
            if ((int) $withdrawal->status !== AffiliateWithdrawal::STATUS_APPROVED) {
                $profile = $this->ensureProfile($withdrawal->affiliateUser);
                $profile->forceFill([
                    'total_balance' => (float) $profile->total_balance + (float) $withdrawal->amount,
                ])->save();
            }

            $withdrawal->forceFill([
                'status' => AffiliateWithdrawal::STATUS_REJECTED,
                'notes' => $notes ?: $withdrawal->notes,
                'processed_at' => now(),
            ])->save();
        });
    }

    public function generateReferralCode(string $seed = 'ref'): string
    {
        $prefix = Str::substr(strtoupper(Str::slug($seed, '')), 0, 8);
        $prefix = $prefix !== '' ? $prefix : 'REF';

        do {
            $code = $prefix.Str::upper(Str::random(4));
        } while (User::query()->where('referral_code', $code)->exists());

        return $code;
    }
}
