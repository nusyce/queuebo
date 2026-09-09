<div class="mx-auto flex w-full max-w-[1680px] flex-col gap-6 px-4 py-4 sm:px-5 xl:px-6">
    <section class="overflow-hidden rounded-[1.9rem] border shadow-[0_30px_80px_-50px_rgba(15,23,42,0.28)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        radial-gradient(circle at top left, rgba(59, 130, 246, 0.12), transparent 28%),
        radial-gradient(circle at top right, rgba(var(--theme-accent-rgb), 0.08), transparent 24%),
        color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
        <div class="flex flex-col gap-5 px-5 py-6 lg:flex-row lg:items-start lg:justify-between lg:px-7">
            <div class="flex min-w-0 items-start gap-4">
                <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-[1.35rem] border shadow-[0_24px_54px_-34px_rgba(15,23,42,0.3)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background: linear-gradient(135deg, rgba(59, 130, 246, 0.18), rgba(59, 130, 246, 0.06)); color: #2563eb;">
                    <i class="fa-light fa-list-check text-xl"></i>
                </span>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.26em]" style="color: var(--theme-muted-text-color);">{{ __('Publishing workspace') }}</p>
                    <h1 class="mt-1 text-[1.85rem] font-semibold tracking-[-0.06em] sm:text-[2.1rem]" style="color: var(--theme-header-text-color);">{{ __('Queue') }}</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-7 sm:text-[15px]" style="color: var(--theme-muted-text-color);">
                        {{ __('Review everything currently lined up for delivery, inspect failures, and jump back into the composer when a queued post needs changes.') }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                <x-ui.button :href="route('portal.publishing.calendar')" variant="outline" size="lg" class="shadow-[0_18px_40px_-30px_rgba(15,23,42,0.22)]" wire:navigate>
                    <i class="fa-light fa-calendar-lines-pen"></i>
                    {{ __('Calendar') }}
                </x-ui.button>
                <x-ui.button :href="route('portal.publishing.drafts')" variant="outline" size="lg" class="shadow-[0_18px_40px_-30px_rgba(15,23,42,0.22)]" wire:navigate>
                    <i class="fa-light fa-file-pen"></i>
                    {{ __('Drafts') }}
                </x-ui.button>
                <x-ui.button :href="route('portal.publishing.calendar')" variant="primary" size="lg" class="shadow-[0_18px_40px_-30px_rgba(var(--theme-accent-rgb),0.35)]" wire:navigate>
                    <i class="fa-light fa-square-plus"></i>
                    {{ __('New Post') }}
                </x-ui.button>
            </div>
        </div>
    </section>

    <section class="rounded-[1.8rem] border px-5 py-5 shadow-[0_24px_60px_-40px_rgba(15,23,42,0.22)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        linear-gradient(180deg, rgba(59, 130, 246, 0.03), transparent 42%),
        color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_20rem_16rem_auto] xl:items-end">
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Queue search') }}</p>
                <x-ui.icon-input
                    wire:model.live.debounce.300ms="search"
                    icon="fa-light fa-magnifying-glass"
                    :placeholder="__('Search by title, caption, or queue id...')"
                    wrapperClass="mt-4"
                    class="h-12 text-sm shadow-[0_18px_40px_-30px_rgba(15,23,42,0.18)]"
                />
            </div>

            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Network') }}</p>
                <div class="mt-4">
                    <x-ui.select wire:model.live="providerFilter" class="[&>div>select]:h-12 [&>div>select]:px-4 [&>div>select]:text-sm [&>div>select]:font-semibold [&>div>select]:shadow-[0_18px_40px_-30px_rgba(15,23,42,0.18)]">
                        <option value="all">{{ __('All networks') }}</option>
                        @foreach ($providerFilters as $provider)
                            <option value="{{ $provider['key'] }}">{{ $provider['label'] }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
            </div>

            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Queue status') }}</p>
                <div class="mt-4">
                    <x-ui.select wire:model.live="statusFilter" class="[&>div>select]:h-12 [&>div>select]:px-4 [&>div>select]:text-sm [&>div>select]:font-semibold [&>div>select]:shadow-[0_18px_40px_-30px_rgba(15,23,42,0.18)]">
                        <option value="all">{{ __('All queue states') }}</option>
                        <option value="scheduled">{{ __('Queued') }}</option>
                        <option value="processing">{{ __('Processing') }}</option>
                        <option value="failed">{{ __('Failed') }}</option>
                    </x-ui.select>
                </div>
            </div>

            <x-ui.button type="button" variant="outline" size="lg" wire:click="clearFilters" class="h-12 shrink-0 shadow-[0_18px_40px_-30px_rgba(15,23,42,0.18)]">
                <i class="fa-light fa-rotate-left mr-2"></i>
                {{ __('Reset') }}
            </x-ui.button>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 2xl:grid-cols-4">
        <div class="rounded-[1.6rem] border px-5 py-5 shadow-[0_24px_60px_-40px_rgba(15,23,42,0.22)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Queue total') }}</p>
            <p class="mt-3 text-[2.2rem] font-semibold tracking-[-0.06em]" style="color: var(--theme-header-text-color);">{{ $summary['total'] }}</p>
            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Posts currently held in the active publishing queue.') }}</p>
        </div>
        <div class="rounded-[1.6rem] border px-5 py-5 shadow-[0_24px_60px_-40px_rgba(15,23,42,0.22)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Queued') }}</p>
            <p class="mt-3 text-[2.2rem] font-semibold tracking-[-0.06em]" style="color: var(--theme-header-text-color);">{{ $summary['scheduled'] }}</p>
            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Scheduled posts that have not started publishing yet.') }}</p>
        </div>
        <div class="rounded-[1.6rem] border px-5 py-5 shadow-[0_24px_60px_-40px_rgba(15,23,42,0.22)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Processing') }}</p>
            <p class="mt-3 text-[2.2rem] font-semibold tracking-[-0.06em]" style="color: var(--theme-header-text-color);">{{ $summary['processing'] }}</p>
            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Posts currently being delivered by the publishing worker.') }}</p>
        </div>
        <div class="rounded-[1.6rem] border px-5 py-5 shadow-[0_24px_60px_-40px_rgba(15,23,42,0.22)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
            linear-gradient(180deg, rgba(239, 68, 68, 0.08), rgba(239, 68, 68, 0.03)),
            color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Failed') }}</p>
            <p class="mt-3 text-[2.2rem] font-semibold tracking-[-0.06em]" style="color: var(--theme-header-text-color);">{{ $summary['failed'] }}</p>
            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Queue items that need attention before they can be published.') }}</p>
        </div>
    </section>

    <section class="rounded-[1.9rem] border p-5 shadow-[0_30px_80px_-50px_rgba(15,23,42,0.28)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
        @if ($queueCards->isEmpty())
            <div class="flex min-h-[18rem] items-center justify-center rounded-[1.4rem] border border-dashed px-6 py-10 text-center" style="border-color: rgba(var(--theme-border-color-rgb), 0.58);">
                <div class="max-w-md">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-[1.15rem]" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.12), rgba(59, 130, 246, 0.04)); color: #2563eb;">
                        <i class="fa-light fa-list-check text-xl"></i>
                    </div>
                    <p class="mt-4 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Queue is empty') }}</p>
                    <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Scheduled and processing posts will appear here once they enter the active publishing queue.') }}</p>
                </div>
            </div>
        @endif

        @if ($queueCards->isNotEmpty())
            @php
                $visibleQueueIds = $queueCards->pluck('id')->values()->all();
                $selectedQueueIds = collect($selectedQueueIds ?? [])->map(fn ($id) => (string) $id)->values();
            @endphp
            <div
                wire:key="queue-selection-{{ md5(json_encode($visibleQueueIds)) }}"
                class="mb-4 flex flex-wrap items-center justify-between gap-3"
                x-data="{
                    visibleQueueIds: @js($visibleQueueIds),
                    selectedQueueIds: @js($selectedQueueIds->all()),
                    selectPage: false,
                    init() {
                        this.syncSelectPage();
                        this.$watch('selectedQueueIds', () => this.syncSelectPage());
                    },
                    allVisibleSelected() {
                        return this.visibleQueueIds.length > 0 && this.visibleQueueIds.every((id) => this.selectedQueueIds.includes(String(id)));
                    },
                    syncSelectPage() {
                        this.selectPage = this.allVisibleSelected();
                    },
                    toggleRow(id) {
                        id = String(id);

                        if (this.selectedQueueIds.includes(id)) {
                            this.selectedQueueIds = this.selectedQueueIds.filter((value) => value !== id);
                        } else {
                            this.selectedQueueIds = [...this.selectedQueueIds, id];
                        }

                        this.syncSelectPage();
                    },
                    toggleVisibleSelection(checked = null) {
                        const shouldSelect = typeof checked === 'boolean' ? checked : !this.allVisibleSelected();

                        if (!shouldSelect) {
                            this.selectedQueueIds = this.selectedQueueIds.filter((id) => !this.visibleQueueIds.includes(String(id)));
                        } else {
                            const merged = new Set([...(this.selectedQueueIds || []), ...this.visibleQueueIds.map(String)]);
                            this.selectedQueueIds = Array.from(merged);
                        }

                        this.syncSelectPage();
                    },
                }"
                x-on:queue-selection-cleared.window="selectedQueueIds = []; selectPage = false"
                x-effect="$wire.set('selectedQueueIds', Array.isArray(selectedQueueIds) ? selectedQueueIds : [], false)"
            >
                <div class="text-sm" style="color: var(--theme-muted-text-color);">
                    <span x-text="selectedQueueIds.length === 1 ? @js(__('1 item selected')) : `${selectedQueueIds.length} {{ __('items selected') }}`"></span>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        x-on:click="toggleVisibleSelection()"
                        data-no-loading
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-[0.95rem] border px-4 text-sm font-semibold transition hover:bg-slate-900/5"
                        style="border-color: rgba(var(--theme-border-color-rgb), 0.68); color: var(--theme-header-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent);"
                    >
                        <i class="fa-light" x-bind:class="allVisibleSelected() ? 'fa-square-minus' : 'fa-square-check'"></i>
                        <span x-text="allVisibleSelected() ? @js(__('Clear page selection')) : @js(__('Select this page'))"></span>
                    </button>
                    <x-ui.dialog :title="__('Delete selected queue items?')" :description="__('This will permanently remove all selected queue items.')" width="sm" dismissible>
                        <x-slot:trigger>
                            <button
                                type="button"
                                x-bind:disabled="selectedQueueIds.length === 0"
                                data-no-loading
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-[0.95rem] border px-4 text-sm font-semibold transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60"
                                style="border-color: rgba(239, 68, 68, 0.24); background-color: rgba(239, 68, 68, 0.08); color: #dc2626;"
                            >
                                <i class="fa-light fa-trash-can"></i>
                                <span x-text="selectedQueueIds.length === 1 ? @js(__('Delete selected (1)')) : `{{ __('Delete selected') }} (${selectedQueueIds.length})`"></span>
                            </button>
                        </x-slot:trigger>
                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-3">
                                <x-ui.button type="button" variant="outline" x-on:click="open = false">
                                    {{ __('Cancel') }}
                                </x-ui.button>
                                <x-ui.button type="button" variant="danger" x-on:click="$wire.deleteSelectedQueueItems(selectedQueueIds); open = false">
                                    {{ __('Delete selected') }}
                                </x-ui.button>
                            </div>
                        </x-slot:footer>
                    </x-ui.dialog>
                </div>

                <div class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.58);">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y" style="divide-color: rgba(var(--theme-border-color-rgb), 0.52);">
                        <thead style="background-color: rgba(var(--theme-border-color-rgb), 0.05);">
                            <tr>
                                <th class="px-4 py-3 text-left">
                                    <x-ui.checkbox
                                        minimal
                                        :checked="false"
                                        x-model="selectPage"
                                        x-on:change="toggleVisibleSelection($event.target.checked)"
                                        data-no-loading
                                    />
                                </th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Channel') }}</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Post') }}</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Media') }}</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Scheduled') }}</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Updated') }}</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Status') }}</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Latest error') }}</th>
                                <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="divide-color: rgba(var(--theme-border-color-rgb), 0.42);">
                                @foreach ($queueCards as $item)
                                @php($providerChipStyle = publishing_provider_chip_style($item['provider_key']))
                                <tr style="background-color: color-mix(in srgb, var(--theme-surface-base) 98%, transparent);">
                                    <td class="px-4 py-4 align-top">
                                        <x-ui.checkbox
                                            minimal
                                            :checked="false"
                                            value="{{ $item['id'] }}"
                                            x-model="selectedQueueIds"
                                            data-no-loading
                                        />
                                    </td>
                                    <td class="px-4 py-4 align-top">
                                        <div class="min-w-[13rem]">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full text-xs" style="{{ $providerChipStyle }}">
                                                    <i class="{{ $item['provider_icon'] }}"></i>
                                                </span>
                                                <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em]" style="{{ $providerChipStyle }}">
                                                    {{ $item['provider_label'] }}
                                                </span>
                                            </div>
                                            <p class="mt-3 text-sm font-semibold leading-6" style="color: var(--theme-header-text-color);">{{ $item['channel'] }}</p>
                                            @if ($item['handle'])
                                                <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ $item['handle'] }}</p>
                                            @endif
                                            <p class="mt-2 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $item['id'] }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 align-top">
                                        <div class="flex min-w-[24rem] items-start gap-4">
                                            @if ($item['media_preview'])
                                                <div class="h-[4.75rem] w-[4.75rem] shrink-0 overflow-hidden rounded-[0.95rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background-color: rgba(var(--theme-border-color-rgb), 0.06);">
                                                    @if (str_starts_with(strtolower($item['media_mime']), 'video/'))
                                                        <video src="{{ $item['media_preview'] }}" muted playsinline preload="metadata" class="h-full w-full object-cover"></video>
                                                    @else
                                                        <img src="{{ $item['media_preview'] }}" alt="{{ $item['title'] }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                                                    @endif
                                                </div>
                                            @endif
                                            <div class="min-w-0 flex-1">
                                                <p class="text-[15px] font-semibold leading-6" style="color: var(--theme-header-text-color); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                    {{ $item['title'] ?: __('Untitled post') }}
                                                </p>
                                                <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color); display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                                    {{ $item['caption'] !== '' ? $item['caption'] : __('No caption written yet.') }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 align-top">
                                        <div class="min-w-[8rem]">
                                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $item['media_type'] }}</p>
                                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ trans_choice(':count file|:count files', $item['media_count'], ['count' => $item['media_count']]) }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 align-top">
                                        <div class="min-w-[9rem] text-sm font-semibold" style="color: var(--theme-header-text-color);">
                                            {{ optional($item['scheduled_at'])->format('d/m/Y H:i') ?: '--' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 align-top">
                                        <div class="min-w-[9rem] text-sm" style="color: var(--theme-header-text-color);">
                                            {{ $item['updated_at'] ? $item['updated_at']->format('d/m/Y H:i') : __('Not available') }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 align-top">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em]" style="{{ $item['status_style'] }}">
                                            {{ $item['status_label'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 align-top">
                                        <div class="min-w-[16rem] max-w-[22rem] text-sm leading-6" style="color: {{ $item['error'] !== '' ? '#b91c1c' : 'var(--theme-muted-text-color)' }};">
                                            {{ $item['error'] !== '' ? \Illuminate\Support\Str::limit($item['error'], 180) : __('No issues') }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 align-top">
                                        <div class="flex min-w-[13.5rem] justify-end gap-2">
                                            @if ($item['can_edit'])
                                                <x-ui.button :href="route('portal.publishing.calendar', ['edit' => $item['id']])" variant="primary" size="sm" class="min-w-[5.25rem] justify-center" wire:navigate>
                                                    <i class="fa-light fa-pen-to-square"></i>
                                                    {{ __('Edit') }}
                                                </x-ui.button>
                                            @endif
                                            <x-ui.dialog :title="__('Delete this queue item?')" :description="__('This will permanently remove the selected queue item.')" width="sm" dismissible>
                                                <x-slot:trigger>
                                                    <button
                                                        type="button"
                                                        class="inline-flex h-10 min-w-[5.75rem] items-center justify-center gap-2 rounded-[0.95rem] border px-3 text-sm font-semibold transition hover:opacity-90"
                                                        style="border-color: rgba(239, 68, 68, 0.24); background-color: rgba(239, 68, 68, 0.08); color: #dc2626;"
                                                        data-no-loading
                                                    >
                                                        <i class="fa-light fa-trash-can"></i>
                                                        {{ __('Delete') }}
                                                    </button>
                                                </x-slot:trigger>
                                                <x-slot:footer>
                                                    <div class="flex items-center justify-end gap-3">
                                                        <x-ui.button type="button" variant="outline" x-on:click="open = false">
                                                            {{ __('Cancel') }}
                                                        </x-ui.button>
                                                        <x-ui.button type="button" variant="danger" wire:click="deleteQueueItem('{{ $item['id'] }}')" x-on:click="open = false">
                                                            {{ __('Delete') }}
                                                        </x-ui.button>
                                                    </div>
                                                </x-slot:footer>
                                            </x-ui.dialog>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-5">
                {{ $items->links() }}
            </div>
            </div>
        @endif
    </section>
</div>
