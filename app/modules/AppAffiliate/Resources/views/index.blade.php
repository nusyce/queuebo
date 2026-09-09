@component(theme_view('layouts.app', 'app'), ['title' => __('Affiliate')])
    @php
        $metricCards = [
            ['label' => __('Clicks'), 'value' => number_format($profile->clicks), 'description' => __('Referral link visits captured from your code.'), 'icon' => 'fa-light fa-arrow-pointer', 'tone' => 'var(--theme-accent)', 'progress' => $profile->clicks > 0 ? 100 : 0],
            ['label' => __('Conversions'), 'value' => number_format($profile->conversions), 'description' => __('Successful commission events attributed to you.'), 'icon' => 'fa-light fa-badge-check', 'tone' => 'rgb(5 150 105)', 'progress' => $profile->conversions > 0 ? 100 : 0],
            ['label' => __('Available Balance'), 'value' => number_format((float) $profile->total_balance, 2), 'description' => __('Current balance available for withdrawal.'), 'icon' => 'fa-light fa-wallet', 'tone' => 'rgb(217 119 6)', 'progress' => (float) $profile->total_balance > 0 ? 100 : 0],
            ['label' => __('Approved Total'), 'value' => number_format((float) $profile->total_approved, 2), 'description' => __('Lifetime approved affiliate earnings.'), 'icon' => 'fa-light fa-sack-dollar', 'tone' => 'rgb(99 102 241)', 'progress' => (float) $profile->total_approved > 0 ? 100 : 0],
        ];
    @endphp

    <div class="space-y-8 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
        

        @if ($errors->has('affiliate_withdrawal'))
            <x-ui.alert :title="__('Unable to submit request')" :description="$errors->first('affiliate_withdrawal')" variant="danger" dismissible />
        @endif

        <section class="overflow-hidden rounded-[1.75rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
            radial-gradient(circle at 0% 0%, rgba(var(--theme-accent-rgb), 0.16), transparent 30%),
            linear-gradient(135deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.98), rgba(var(--theme-surface-base-rgb,255,255,255),0.94));
        ">
            <div class="grid gap-6 px-6 py-7 sm:px-8 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-start">
                <div class="flex items-start gap-4">
                    <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-[1.35rem]" style="background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                        <i class="fa-light fa-hand-holding-dollar text-lg"></i>
                    </span>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-3">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-accent);">{{ __('User portal') }}</p>
                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em]" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.74); color: var(--theme-muted-text-color);">
                                {{ __('Affiliate program') }}
                            </span>
                        </div>

                        <h1 class="mt-2 text-[1.85rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ __('Affiliate') }}</h1>
                        <p class="mt-3 max-w-3xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Share your referral link, track commissions, and request withdrawals from one place.') }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 lg:justify-end">
                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-2 text-sm font-medium" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.76); color: var(--theme-header-text-color); box-shadow: 0 12px 30px -24px rgba(15,23,42,0.28);">
                        <i class="fa-light fa-wallet"></i>
                        {{ __('Balance: :amount', ['amount' => number_format((float) $profile->total_balance, 2)]) }}
                    </span>
                </div>
            </div>
        </section>

        <x-ui.metric-strip :items="$metricCards" :show-progress="false" columns="md:grid-cols-2 xl:grid-cols-4" />

        <div class="grid gap-5 xl:grid-cols-2 xl:items-stretch">
            <x-ui.card class="flex h-full flex-col space-y-5">
                <div>
                    <h3 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Referral assets') }}</h3>
                    <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Use this code or direct link anywhere you promote your referral offer.') }}</p>
                </div>

                <div class="space-y-4">
                    <x-ui.input :label="__('Referral code')" :value="$referralCode" readonly />
                    <x-ui.input :label="__('Referral link')" :value="$referralLink" readonly />
                </div>

                <div class="mt-auto rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-border-color-rgb), 0.03);">
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Sharing note') }}</p>
                    <p class="mt-2 text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Use the full link in social posts, bios, direct messages, and email campaigns so every referral visit is tracked correctly.') }}</p>
                </div>
            </x-ui.card>

            <x-ui.card class="flex h-full flex-col space-y-5">
                <div>
                    <h3 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Request withdrawal') }}</h3>
                    <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Minimum withdrawal: :amount', ['amount' => number_format($minimumWithdrawal, 2)]) }}</p>
                </div>

                <form method="POST" action="{{ route('portal.affiliate.withdraw') }}" class="flex h-full flex-col space-y-4">
                    @csrf
                    <x-ui.input name="amount" type="number" step="0.01" min="0.01" :label="__('Amount')" :value="old('amount')" :error="$errors->first('amount')" required />
                    <x-ui.input name="payment_method" :label="__('Payment method')" :value="old('payment_method')" :error="$errors->first('payment_method')" :placeholder="__('Bank transfer, PayPal, USDT...')" required />
                    <x-ui.textarea name="payment_details" :label="__('Payment details')" :error="$errors->first('payment_details')" rows="5" :placeholder="__('Account number, wallet address, account holder, and payout notes.')">{{ old('payment_details') }}</x-ui.textarea>
                    <div class="mt-auto pt-1">
                        <x-ui.button type="submit">{{ __('Submit request') }}</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <div class="grid gap-5 xl:grid-cols-2 xl:items-stretch">
            <x-ui.datatable-shell class="h-full" :title="__('Recent commissions')" :info="__('Your latest affiliate commission records.')">
                <x-ui.table class="rounded-none border-0 shadow-none">
                    <x-ui.table-head>
                        <x-ui.table-cell head>{{ __('User') }}</x-ui.table-cell>
                        <x-ui.table-cell head>{{ __('Payment') }}</x-ui.table-cell>
                        <x-ui.table-cell head>{{ __('Commission') }}</x-ui.table-cell>
                        <x-ui.table-cell head>{{ __('Status') }}</x-ui.table-cell>
                    </x-ui.table-head>
                    <x-ui.table-body>
                        @forelse ($commissions as $commission)
                            <x-ui.table-row>
                                <x-ui.table-cell><div class="space-y-1"><p class="font-medium" style="color: var(--theme-header-text-color);">{{ $commission->referredUser?->name ?: __('Unknown user') }}</p><p class="text-xs" style="color: var(--theme-muted-text-color);">{{ $commission->referredUser?->email ?: __('No email') }}</p></div></x-ui.table-cell>
                                <x-ui.table-cell><div class="space-y-1"><p class="text-sm" style="color: var(--theme-header-text-color);">{{ $commission->paymentHistory?->transaction_id ?: __('N/A') }}</p><p class="text-xs" style="color: var(--theme-muted-text-color);">{{ strtoupper((string) ($commission->paymentHistory?->currency ?: 'USD')) }} Â· {{ $commission->paymentHistory?->from ?: __('Unknown') }}</p></div></x-ui.table-cell>
                                <x-ui.table-cell><div class="space-y-1"><p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((float) $commission->commission, 2) }}</p><p class="text-xs" style="color: var(--theme-muted-text-color);">{{ number_format((float) $commission->commission_rate, 2) }}%</p></div></x-ui.table-cell>
                                <x-ui.table-cell><x-ui.badge :variant="$commission->statusVariant()">{{ $commission->statusLabel() }}</x-ui.badge></x-ui.table-cell>
                            </x-ui.table-row>
                        @empty
                            <x-ui.table-row>
                                <x-ui.table-cell colspan="4" class="py-10">
                                    <x-ui.empty icon="fa-light fa-badge-dollar" :title="__('No commissions yet.')" :description="__('Once referred users complete eligible payments, the records will show here.')" />
                                </x-ui.table-cell>
                            </x-ui.table-row>
                        @endforelse
                    </x-ui.table-body>
                </x-ui.table>
            </x-ui.datatable-shell>

            <x-ui.datatable-shell class="h-full" :title="__('Withdrawal history')" :info="__('Your latest withdrawal requests and payout status.')">
                <x-ui.table class="rounded-none border-0 shadow-none">
                    <x-ui.table-head>
                        <x-ui.table-cell head>{{ __('Request') }}</x-ui.table-cell>
                        <x-ui.table-cell head>{{ __('Method') }}</x-ui.table-cell>
                        <x-ui.table-cell head>{{ __('Amount') }}</x-ui.table-cell>
                        <x-ui.table-cell head>{{ __('Status') }}</x-ui.table-cell>
                    </x-ui.table-head>
                    <x-ui.table-body>
                        @forelse ($withdrawals as $withdrawal)
                            <x-ui.table-row>
                                <x-ui.table-cell><div class="space-y-1"><p class="font-medium" style="color: var(--theme-header-text-color);">{{ $withdrawal->id_secure }}</p><p class="text-xs" style="color: var(--theme-muted-text-color);">{{ $withdrawal->createdAtFormatted() ?: __('N/A') }}</p></div></x-ui.table-cell>
                                <x-ui.table-cell><div class="space-y-1"><p class="text-sm" style="color: var(--theme-header-text-color);">{{ $withdrawal->payment_method }}</p><p class="line-clamp-2 text-xs" style="color: var(--theme-muted-text-color);">{{ $withdrawal->payment_details }}</p></div></x-ui.table-cell>
                                <x-ui.table-cell><span class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((float) $withdrawal->amount, 2) }}</span></x-ui.table-cell>
                                <x-ui.table-cell><x-ui.badge :variant="$withdrawal->statusVariant()">{{ $withdrawal->statusLabel() }}</x-ui.badge></x-ui.table-cell>
                            </x-ui.table-row>
                        @empty
                            <x-ui.table-row>
                                <x-ui.table-cell colspan="4" class="py-10">
                                    <x-ui.empty icon="fa-light fa-money-bill-transfer" :title="__('No withdrawals yet.')" :description="__('Your submitted payout requests will appear here.')" />
                                </x-ui.table-cell>
                            </x-ui.table-row>
                        @endforelse
                    </x-ui.table-body>
                </x-ui.table>
            </x-ui.datatable-shell>
        </div>
    </div>
@endcomponent
