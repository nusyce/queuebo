@component(theme_view('layouts.app', 'app'), ['title' => __('Credit Usage')])
    @php($activeAction = request('action', ''))

    <div class="space-y-6">
        <x-ui.sub-header
            :eyebrow="__('AI Studio')"
            :title="__('Credit Usage')"
            :description="__('Track how credits are consumed across caption generation, rewrite, planning, review, and media jobs.')"
            :count="$logs->total()"
        >
            <x-slot:actions>
                @if ($canBuyMoreCredits && $creditPacks->isNotEmpty())
                    <x-ui.button :href="route('portal.credits').'#credit-packs'" wire:navigate>
                        {{ __('Buy more credits') }}
                    </x-ui.button>
                @endif
                <x-ui.button :href="route('portal.ai-studio.prompt-history')" variant="outline" wire:navigate>
                    {{ __('Prompt History') }}
                </x-ui.button>
                <x-ui.button :href="route('portal.ai-studio.settings')" variant="outline" wire:navigate>
                    {{ __('AI Settings') }}
                </x-ui.button>
            </x-slot:actions>
        </x-ui.sub-header>

        <section class="grid gap-5 md:grid-cols-4">
            <x-ui.card class="space-y-2">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">{{ __('Entries') }}</p>
                <p class="text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($entriesCount) }}</p>
                <p class="text-sm leading-6 text-slate-500 dark:text-slate-400">{{ __('Logged AI Studio credit events.') }}</p>
            </x-ui.card>
            <x-ui.card class="space-y-2">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">{{ __('Credits used') }}</p>
                <p class="text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($creditsUsed) }}</p>
                <p class="text-sm leading-6 text-slate-500 dark:text-slate-400">{{ __('Total credits consumed by AI Studio actions.') }}</p>
            </x-ui.card>
            <x-ui.card class="space-y-2">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">{{ __('Actions used') }}</p>
                <p class="text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($actionsCount) }}</p>
                <p class="text-sm leading-6 text-slate-500 dark:text-slate-400">{{ __('Distinct AI Studio actions that consumed credits.') }}</p>
            </x-ui.card>
            <x-ui.card class="space-y-2">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">{{ __('Remaining') }}</p>
                <p class="text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ ($creditSummary['unlimited'] ?? false) ? __('Unlimited') : number_format((int) ($creditSummary['remaining'] ?? 0)) }}</p>
                <p class="text-sm leading-6 text-slate-500 dark:text-slate-400">{{ __('Credits still available for the current plan period.') }}</p>
            </x-ui.card>
        </section>

        @if ($canBuyMoreCredits && $creditPacks->isNotEmpty())
            <x-ui.card id="credit-packs" class="space-y-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Top-up store') }}</p>
                        <h3 class="mt-2 text-[1.3rem] font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ __('Buy more credits') }}</h3>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">
                            {{ __('Top-up credits are cumulative, non-expiring, and can be spent before plan credits when the engine priority is configured that way.') }}
                        </p>
                    </div>
                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: var(--theme-border-color); background: var(--theme-surface-soft);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Top-up balance') }}</p>
                        <p class="mt-2 text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) $creditTopupRemaining) }}</p>
                    </div>
                </div>

                <div class="grid gap-5 lg:grid-cols-3">
                    @foreach ($creditPacks as $pack)
                        <div class="rounded-[1.15rem] border p-5" style="border-color: var(--theme-border-color); background: var(--theme-surface-base);">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $pack->name }}</p>
                                    @if ($pack->description)
                                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $pack->description }}</p>
                                    @endif
                                </div>
                                @if ($pack->featured)
                                    <x-ui.badge variant="success">{{ __('Featured') }}</x-ui.badge>
                                @endif
                            </div>

                            <div class="mt-4 flex items-end justify-between gap-4">
                                <div>
                                    <p class="text-3xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) $pack->credits) }}</p>
                                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Credits') }}</p>
                                </div>
                                <p class="text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ $pack->currency_symbol }}{{ number_format((float) $pack->price, 2) }}</p>
                            </div>

                            <div class="mt-5">
                                <x-ui.button :href="route('payment.credits', $pack->slug)" class="w-full justify-center" wire:navigate>
                                    {{ __('Buy more credits') }}
                                </x-ui.button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>
        @endif

        <x-ui.card class="space-y-4">
            <form method="GET" action="{{ route('portal.credits') }}" class="grid gap-4 md:grid-cols-[240px_auto]">
                <x-ui.select name="action" :label="__('Action')">
                    <option value="">{{ __('All actions') }}</option>
                    @foreach ($actionOptions as $key => $label)
                        <option value="{{ $key }}" @selected($activeAction === $key)>{{ $label }}</option>
                    @endforeach
                </x-ui.select>
                <div class="flex items-end gap-3">
                    <x-ui.button type="submit">{{ __('Filter') }}</x-ui.button>
                    <x-ui.button :href="route('portal.credits')" variant="outline" wire:navigate>{{ __('Reset') }}</x-ui.button>
                </div>
            </form>
        </x-ui.card>

        @if ($topupLedger->isNotEmpty())
            <x-ui.datatable-shell :title="__('Top-up ledger')" :info="__('Credit pack purchases and top-up consumption entries recorded on this account.')">
                <x-ui.table class="rounded-none border-0 shadow-none">
                    <x-ui.table-head>
                        <x-ui.table-cell head>{{ __('Type') }}</x-ui.table-cell>
                        <x-ui.table-cell head>{{ __('Amount') }}</x-ui.table-cell>
                        <x-ui.table-cell head>{{ __('Remaining') }}</x-ui.table-cell>
                        <x-ui.table-cell head>{{ __('Pack') }}</x-ui.table-cell>
                        <x-ui.table-cell head>{{ __('Time') }}</x-ui.table-cell>
                    </x-ui.table-head>
                    <x-ui.table-body>
                        @foreach ($topupLedger as $entry)
                            <x-ui.table-row>
                                <x-ui.table-cell>
                                    <x-ui.badge :variant="in_array($entry->type, ['purchase', 'adjustment'], true) ? 'success' : 'neutral'">
                                        {{ __(ucfirst(str_replace('_', ' ', $entry->type))) }}
                                    </x-ui.badge>
                                </x-ui.table-cell>
                                <x-ui.table-cell>
                                    <span class="font-semibold" style="color: var(--theme-header-text-color);">
                                        {{ $entry->amount > 0 ? '+' : '' }}{{ number_format((int) $entry->amount) }}
                                    </span>
                                </x-ui.table-cell>
                                <x-ui.table-cell>{{ number_format((int) $entry->remaining) }}</x-ui.table-cell>
                                <x-ui.table-cell>{{ $entry->creditPack?->name ?: __('Manual adjustment') }}</x-ui.table-cell>
                                <x-ui.table-cell>{{ $entry->created_at?->format('Y-m-d H:i:s') }}</x-ui.table-cell>
                            </x-ui.table-row>
                        @endforeach
                    </x-ui.table-body>
                </x-ui.table>
            </x-ui.datatable-shell>
        @endif

        <x-ui.datatable-shell :title="__('Usage log')" :info="__('Each row represents credits consumed by one AI Studio action.')">
            <x-ui.table class="rounded-none border-0 shadow-none">
                <x-ui.table-head>
                    <x-ui.table-cell head>{{ __('Action') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Credits') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Quantity') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Balance') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Metadata') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Time') }}</x-ui.table-cell>
                </x-ui.table-head>
                <x-ui.table-body>
                    @forelse ($logs as $log)
                        <x-ui.table-row>
                            <x-ui.table-cell>
                                <div class="space-y-1">
                                    <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $actionOptions[$log->action_key] ?? $log->action_key }}</p>
                                    @if ($log->feature)
                                        <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ $log->feature }}</p>
                                    @endif
                                </div>
                            </x-ui.table-cell>
                            <x-ui.table-cell><span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) $log->amount) }}</span></x-ui.table-cell>
                            <x-ui.table-cell>{{ number_format((int) $log->quantity) }}</x-ui.table-cell>
                            <x-ui.table-cell>
                                @if ($log->is_unlimited)
                                    <x-ui.badge variant="success">{{ __('Unlimited') }}</x-ui.badge>
                                @else
                                    <div class="space-y-1 text-sm">
                                        <p style="color: var(--theme-header-text-color);">{{ __('Before: :value', ['value' => number_format((int) $log->credits_before)]) }}</p>
                                        <p style="color: var(--theme-muted-text-color);">{{ __('After: :value', ['value' => number_format((int) $log->credits_after)]) }}</p>
                                    </div>
                                @endif
                            </x-ui.table-cell>
                            <x-ui.table-cell>
                                <p class="text-xs leading-6" style="color: var(--theme-muted-text-color);">
                                    {{ collect((array) $log->metadata)->map(fn ($value, $key) => $key.': '.(is_array($value) ? implode(', ', array_map('strval', $value)) : (is_scalar($value) || $value === null ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))))->implode(' • ') ?: '—' }}
                                </p>
                            </x-ui.table-cell>
                            <x-ui.table-cell>{{ $log->created_at?->format('Y-m-d H:i:s') }}</x-ui.table-cell>
                        </x-ui.table-row>
                    @empty
                        <x-ui.table-row>
                            <x-ui.table-cell colspan="6" class="py-10 text-center text-zinc-500 dark:text-zinc-400">
                                {{ __('No credit usage logged yet.') }}
                            </x-ui.table-cell>
                        </x-ui.table-row>
                    @endforelse
                </x-ui.table-body>
            </x-ui.table>
        </x-ui.datatable-shell>

        @if ($logs->hasPages())
            <div>
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endcomponent
