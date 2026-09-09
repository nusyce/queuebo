@component(theme_view('layouts.app', 'app'), ['title' => __('Activity Log')])
    @php
        $formatMetadataValue = static function (mixed $value): string {
            if (is_array($value)) {
                return collect($value)
                    ->map(fn (mixed $nestedValue, mixed $nestedKey) => is_string($nestedKey)
                        ? $nestedKey.': '.(is_scalar($nestedValue) || $nestedValue === null ? (string) $nestedValue : json_encode($nestedValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                        : (is_scalar($nestedValue) || $nestedValue === null ? (string) $nestedValue : json_encode($nestedValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)))
                    ->implode(', ');
            }

            if (is_object($value)) {
                $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                return $json !== false ? $json : '[object]';
            }

            if (is_bool($value)) {
                return $value ? 'true' : 'false';
            }

            return $value === null ? '-' : (string) $value;
        };
    @endphp

    <div class="space-y-6">
        <x-ui.sub-header
            :eyebrow="__('User portal')"
            :title="__('Activity Log')"
            :description="__('Recent actions performed by your account across the user portal and admin workspace.')"
            :count="$logs->count()"
        >
            <x-slot:actions>
                <x-ui.button href="{{ route('portal.dashboard') }}" variant="outline" wire:navigate>
                    {{ __('Back to dashboard') }}
                </x-ui.button>
            </x-slot:actions>
        </x-ui.sub-header>

        <section class="grid gap-5 md:grid-cols-3">
            <x-ui.card class="space-y-2">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">{{ __('Entries') }}</p>
                <p class="text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($logs->count()) }}</p>
                <p class="text-sm leading-6 text-slate-500 dark:text-slate-400">{{ __('Recent activity records loaded for your account.') }}</p>
            </x-ui.card>

            <x-ui.card class="space-y-2">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">{{ __('Latest event') }}</p>
                <p class="text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ $logs->first()->event ?? __('No activity yet') }}</p>
                <p class="text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $logs->first()?->created_at?->format('Y-m-d H:i') ?? __('Activity will appear here after key actions.') }}</p>
            </x-ui.card>

            <x-ui.card class="space-y-2">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">{{ __('Areas') }}</p>
                <p class="text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($logs->pluck('area')->unique()->count()) }}</p>
                <p class="text-sm leading-6 text-slate-500 dark:text-slate-400">{{ __('Portal and admin actions are recorded together for this user.') }}</p>
            </x-ui.card>
        </section>

        <x-ui.datatable-shell :title="__('Recent activity')" :info="__('Chronological action history for your account.')">
            <x-ui.table class="rounded-none border-0 shadow-none">
                <x-ui.table-head>
                    <x-ui.table-cell head>{{ __('Event') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Description') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Area') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Route') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Time') }}</x-ui.table-cell>
                </x-ui.table-head>

                <x-ui.table-body>
                    @forelse ($logs as $log)
                        <x-ui.table-row>
                            <x-ui.table-cell>
                                <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $log->event }}</p>
                            </x-ui.table-cell>
                            <x-ui.table-cell>
                                <div class="space-y-1">
                                    <p class="font-medium" style="color: var(--theme-header-text-color);">{{ $log->description ?: '—' }}</p>
                                    @if (! empty($log->metadata))
                                        <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ collect($log->metadata)->map(fn ($value, $key) => $key.': '.$formatMetadataValue($value))->implode(' · ') }}</p>
                                    @endif
                                </div>
                            </x-ui.table-cell>
                            <x-ui.table-cell><x-ui.badge :variant="$log->area === 'user' ? 'primary' : 'neutral'">{{ strtoupper($log->area) }}</x-ui.badge></x-ui.table-cell>
                            <x-ui.table-cell><span class="font-mono text-xs" style="color: var(--theme-muted-text-color);">{{ $log->route_name ?: '—' }}</span></x-ui.table-cell>
                            <x-ui.table-cell>{{ $log->created_at?->format('Y-m-d H:i:s') }}</x-ui.table-cell>
                        </x-ui.table-row>
                    @empty
                        <x-ui.table-row>
                            <x-ui.table-cell colspan="5" class="py-10 text-center text-zinc-500 dark:text-zinc-400">
                                {{ __('No activity recorded yet.') }}
                            </x-ui.table-cell>
                        </x-ui.table-row>
                    @endforelse
                </x-ui.table-body>
            </x-ui.table>
        </x-ui.datatable-shell>
    </div>
@endcomponent
