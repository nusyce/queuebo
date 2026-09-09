@component(theme_view('layouts.app', 'app'), ['title' => __('Prompt History')])
    @php
        $activeModule = request('module', '');
        $search = request('q', '');
    @endphp

    <div class="space-y-6">
        

        <x-ui.sub-header
            :eyebrow="__('AI Studio')"
            :title="__('Prompt History')"
            :description="__('Rename, reopen, and clean up the prompts you have already used across AI Studio modules.')"
            :count="$histories->total()"
        >
            <x-slot:actions>
                <x-ui.button :href="route('portal.ai-studio.settings')" variant="outline" wire:navigate>
                    {{ __('AI Settings') }}
                </x-ui.button>
                <x-ui.button :href="route('portal.credits')" variant="outline" wire:navigate>
                    {{ __('Credit Usage') }}
                </x-ui.button>
            </x-slot:actions>
        </x-ui.sub-header>

        <section class="grid gap-5 md:grid-cols-3">
            <x-ui.card class="space-y-2">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">{{ __('Stored prompts') }}</p>
                <p class="text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($histories->total()) }}</p>
                <p class="text-sm leading-6 text-slate-500 dark:text-slate-400">{{ __('Saved prompts available to reopen and reuse.') }}</p>
            </x-ui.card>

            <x-ui.card class="space-y-2">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">{{ __('Modules') }}</p>
                <p class="text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format(count($modules)) }}</p>
                <p class="text-sm leading-6 text-slate-500 dark:text-slate-400">{{ __('Caption, planner, review, media, and rewrite tools share one prompt archive.') }}</p>
            </x-ui.card>

            <x-ui.card class="space-y-2">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">{{ __('Credits left') }}</p>
                <p class="text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ ($creditSummary['unlimited'] ?? false) ? __('Unlimited') : number_format((int) ($creditSummary['remaining'] ?? 0)) }}</p>
                <p class="text-sm leading-6 text-slate-500 dark:text-slate-400">{{ __('Current workspace credit balance for AI Studio actions.') }}</p>
            </x-ui.card>
        </section>

        <x-ui.card class="space-y-4">
            <form method="GET" action="{{ route('portal.ai-studio.prompt-history') }}" class="grid gap-4 md:grid-cols-[minmax(0,1fr)_240px_auto]">
                <x-ui.input name="q" :label="__('Search')" :value="$search" :placeholder="__('Prompt title or content...')" />
                <x-ui.select name="module" :label="__('Module')">
                    <option value="">{{ __('All modules') }}</option>
                    @foreach ($modules as $key => $label)
                        <option value="{{ $key }}" @selected($activeModule === $key)>{{ $label }}</option>
                    @endforeach
                </x-ui.select>
                <div class="flex items-end gap-3">
                    <x-ui.button type="submit">{{ __('Filter') }}</x-ui.button>
                    <x-ui.button :href="route('portal.ai-studio.prompt-history')" variant="outline" wire:navigate>{{ __('Reset') }}</x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <div class="space-y-4">
            @forelse ($histories as $history)
                <x-ui.card class="space-y-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-ui.badge variant="primary">{{ $modules[$history->module] ?? ucfirst(str_replace('_', ' ', $history->module)) }}</x-ui.badge>
                                @if (! empty($history->config['language']))
                                    <x-ui.badge variant="neutral">{{ collect(world_languages())->firstWhere('code', data_get($history->config, 'language'))['name'] ?? strtoupper((string) data_get($history->config, 'language')) }}</x-ui.badge>
                                @endif
                                <span class="text-xs" style="color: var(--theme-muted-text-color);">{{ $history->created_at?->format('Y-m-d H:i') }}</span>
                            </div>

                            <h2 class="mt-3 text-lg font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">
                                {{ $history->title ?: __('Untitled prompt') }}
                            </h2>

                            <p class="mt-2 text-sm leading-7" style="color: var(--theme-muted-text-color);">
                                {{ \Illuminate\Support\Str::limit($history->prompt, 240) }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <x-ui.modal width="lg" :title="__('Rename prompt history')" :description="__('Update the label shown in your prompt archive without changing the saved prompt body.')">
                                <x-slot:trigger>
                                    <x-ui.button type="button" variant="outline">{{ __('Rename') }}</x-ui.button>
                                </x-slot:trigger>

                                <form id="rename-prompt-history-{{ $history->id }}" method="POST" action="{{ route('portal.ai-studio.prompt-history.update', $history->id) }}" class="space-y-5">
                                    @csrf
                                    @method('PUT')
                                    <x-ui.input name="title" :label="__('Title')" :value="$history->title ?: __('Prompt history')" required />
                                </form>

                                <x-slot:footer>
                                    <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                    <x-ui.button type="submit" form="rename-prompt-history-{{ $history->id }}">{{ __('Save title') }}</x-ui.button>
                                </x-slot:footer>
                            </x-ui.modal>

                            <x-ui.dialog :title="__('Delete this prompt history?')" :description="__('This permanently removes the saved prompt entry from your AI Studio history.')" width="sm" dismissible>
                                <x-slot:trigger>
                                    <x-ui.button type="button" variant="danger">{{ __('Delete') }}</x-ui.button>
                                </x-slot:trigger>

                                <x-slot:footer>
                                    <div class="flex justify-end gap-3">
                                        <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                        <form method="POST" action="{{ route('portal.ai-studio.prompt-history.destroy', $history->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button type="submit" variant="danger" x-on:click="open = false">{{ __('Delete prompt') }}</x-ui.button>
                                        </form>
                                    </div>
                                </x-slot:footer>
                            </x-ui.dialog>
                        </div>
                    </div>

                    <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_22rem]">
                        <div class="rounded-[1rem] border p-4" style="border-color: var(--theme-border-color); background: var(--theme-surface-base);">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Prompt body') }}</p>
                            <p class="mt-3 whitespace-pre-line text-sm leading-7" style="color: var(--theme-header-text-color);">{{ $history->prompt }}</p>
                        </div>

                        <div class="space-y-4">
                            <div class="rounded-[1rem] border p-4" style="border-color: var(--theme-border-color); background: var(--theme-surface-base);">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Input config') }}</p>
                                <div class="mt-3 space-y-2 text-sm" style="color: var(--theme-header-text-color);">
                                    @foreach ((array) $history->input_payload as $key => $value)
                                        <p><span class="font-semibold">{{ str($key)->replace('_', ' ')->title() }}:</span> {{ is_array($value) ? implode(', ', array_map('strval', $value)) : (is_scalar($value) || $value === null ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}</p>
                                    @endforeach
                                </div>
                            </div>

                            <div class="rounded-[1rem] border p-4" style="border-color: var(--theme-border-color); background: var(--theme-surface-base);">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Output snapshot') }}</p>
                                <p class="mt-3 text-sm leading-7" style="color: var(--theme-muted-text-color);">
                                    {{ \Illuminate\Support\Str::limit(json_encode($history->output_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 280) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </x-ui.card>
            @empty
                <x-ui.empty
                    class="rounded-[1.3rem] border px-6 py-10 sm:px-7"
                    icon="fa-light fa-clock-rotate-left"
                    :title="__('No prompt history yet')"
                    :description="__('Generated prompts will appear here after you use AI Studio modules.')"
                />
            @endforelse
        </div>

        @if ($histories->hasPages())
            <div>
                {{ $histories->links() }}
            </div>
        @endif
    </div>
@endcomponent
