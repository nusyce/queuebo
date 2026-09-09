@php
    $publishingApprovalTeam = \Modules\AppTeams\Support\TeamWorkspaceAccess::activeTeam(auth()->user());
    $publishingCanApprovePosts = auth()->user()
        && $publishingApprovalTeam
        && \Modules\AppTeams\Support\TeamWorkspaceAccess::hasPermission(auth()->user(), 'post.approve', $publishingApprovalTeam);
@endphp

<div class="mx-auto flex w-full max-w-[1680px] flex-col gap-6 px-4 py-4 sm:px-5 xl:px-6">
    <section class="overflow-hidden rounded-[1.9rem] border shadow-[0_30px_80px_-50px_rgba(15,23,42,0.28)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        radial-gradient(circle at top left, rgba(var(--theme-accent-rgb), 0.14), transparent 28%),
        radial-gradient(circle at top right, rgba(56, 189, 248, 0.10), transparent 24%),
        color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
        <div class="flex flex-col gap-5 px-5 py-6 lg:flex-row lg:items-start lg:justify-between lg:px-7">
            <div class="flex min-w-0 items-start gap-4">
                <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-[1.35rem] border shadow-[0_24px_54px_-34px_rgba(15,23,42,0.3)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb), 0.18), rgba(var(--theme-accent-rgb), 0.06)); color: var(--theme-accent);">
                    <i class="fa-light fa-file-pen text-xl"></i>
                </span>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.26em]" style="color: var(--theme-muted-text-color);">{{ __('Publishing workspace') }}</p>
                    <h1 class="mt-1 text-[1.85rem] font-semibold tracking-[-0.06em] sm:text-[2.1rem]" style="color: var(--theme-header-text-color);">{{ __('Drafts') }}</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-7 sm:text-[15px]" style="color: var(--theme-muted-text-color);">
                        {{ __('Manage unpublished posts in one queue, then jump back into the composer only when a draft is ready to finish.') }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                <x-ui.button :href="route('portal.publishing.calendar')" variant="outline" size="lg" class="shadow-[0_18px_40px_-30px_rgba(15,23,42,0.22)]" wire:navigate>
                    <i class="fa-light fa-calendar-lines-pen"></i>
                    {{ __('Calendar') }}
                </x-ui.button>
                <x-ui.button :href="route('portal.publishing.queue')" variant="outline" size="lg" class="shadow-[0_18px_40px_-30px_rgba(15,23,42,0.22)]" wire:navigate>
                    <i class="fa-light fa-list-check"></i>
                    {{ __('Queue') }}
                </x-ui.button>
                @if ($publishingCanApprovePosts)
                    <x-ui.button :href="route('portal.publishing.approvals')" variant="outline" size="lg" class="shadow-[0_18px_40px_-30px_rgba(15,23,42,0.22)]" wire:navigate>
                        <i class="fa-light fa-badge-check"></i>
                        {{ __('Approvals') }}
                    </x-ui.button>
                @endif
                <x-ui.button :href="route('portal.publishing.calendar')" variant="primary" size="lg" class="shadow-[0_18px_40px_-30px_rgba(var(--theme-accent-rgb),0.35)]" wire:navigate>
                    <i class="fa-light fa-square-plus"></i>
                    {{ __('New Post') }}
                </x-ui.button>
            </div>
        </div>
    </section>

    <section class="rounded-[1.8rem] border px-5 py-5 shadow-[0_24px_60px_-40px_rgba(15,23,42,0.22)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        linear-gradient(180deg, rgba(var(--theme-accent-rgb), 0.03), transparent 42%),
        color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_30rem] xl:items-start">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Draft filters') }}</p>
                <x-ui.icon-input
                    wire:model.live.debounce.300ms="search"
                    icon="fa-light fa-magnifying-glass"
                    :placeholder="__('Search by title, caption, or draft id...')"
                    wrapperClass="mt-4"
                    class="h-12 text-sm shadow-[0_18px_40px_-30px_rgba(15,23,42,0.18)]"
                />
            </div>

            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Network view') }}</p>

                <div class="mt-4 flex items-center gap-2">
                    <div class="min-w-0 flex-1">
                        <x-ui.select wire:model.live="providerFilter" class="[&>div>select]:h-12 [&>div>select]:px-4 [&>div>select]:text-sm [&>div>select]:font-semibold [&>div>select]:shadow-[0_18px_40px_-30px_rgba(15,23,42,0.18)]">
                            <option value="all">{{ __('All networks') }}</option>
                            @foreach ($providerFilters as $provider)
                                <option value="{{ $provider['key'] }}">{{ $provider['label'] }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    <x-ui.button type="button" variant="outline" size="lg" wire:click="clearFilters" class="h-12 shrink-0 shadow-[0_18px_40px_-30px_rgba(15,23,42,0.18)]">
                        <i class="fa-light fa-rotate-left mr-2"></i>
                        {{ __('Reset') }}
                    </x-ui.button>
                </div>
            </div>
        </div>

        <p class="mt-4 text-sm leading-6" style="color: var(--theme-muted-text-color);">
            {{ __('Keep draft work out of the calendar so scheduled lanes only show active publishing slots.') }}
        </p>
    </section>

    <section class="grid gap-4 md:grid-cols-2 2xl:grid-cols-4">
        <div class="rounded-[1.6rem] border px-5 py-5 shadow-[0_24px_60px_-40px_rgba(15,23,42,0.22)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Draft queue') }}</p>
            <p class="mt-3 text-[2.2rem] font-semibold tracking-[-0.06em]" style="color: var(--theme-header-text-color);">{{ $summary['total'] }}</p>
            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Draft records matching the current filters.') }}</p>
        </div>
        <div class="rounded-[1.6rem] border px-5 py-5 shadow-[0_24px_60px_-40px_rgba(15,23,42,0.22)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Networks') }}</p>
            <p class="mt-3 text-[2.2rem] font-semibold tracking-[-0.06em]" style="color: var(--theme-header-text-color);">{{ $summary['providers'] }}</p>
            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Connected providers represented inside this draft queue.') }}</p>
        </div>
        <div class="rounded-[1.6rem] border px-5 py-5 shadow-[0_24px_60px_-40px_rgba(15,23,42,0.22)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('With media') }}</p>
            <p class="mt-3 text-[2.2rem] font-semibold tracking-[-0.06em]" style="color: var(--theme-header-text-color);">{{ $summary['with_media'] }}</p>
            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Drafts already carrying at least one uploaded media item.') }}</p>
        </div>
        <div class="rounded-[1.6rem] border px-5 py-5 shadow-[0_24px_60px_-40px_rgba(15,23,42,0.22)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
            linear-gradient(180deg, rgba(var(--theme-accent-rgb), 0.06), rgba(56, 189, 248, 0.04)),
            color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Pending review') }}</p>
            <p class="mt-3 text-[2.2rem] font-semibold tracking-[-0.06em]" style="color: var(--theme-header-text-color);">{{ $summary['pending_review'] }}</p>
            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Drafts still waiting for review approval inside the team workflow.') }}</p>
        </div>
    </section>

    <section class="rounded-[1.9rem] border p-5 shadow-[0_30px_80px_-50px_rgba(15,23,42,0.28)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
        

        @if ($draftCards->isEmpty())
            <div class="flex min-h-[18rem] items-center justify-center rounded-[1.4rem] border border-dashed px-6 py-10 text-center" style="border-color: rgba(var(--theme-border-color-rgb), 0.58);">
                <div class="max-w-md">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-[1.15rem]" style="background: linear-gradient(135deg, rgba(var(--theme-accent-rgb), 0.12), rgba(var(--theme-accent-rgb), 0.04)); color: var(--theme-accent);">
                        <i class="fa-light fa-file-circle-plus text-xl"></i>
                    </div>
                    <p class="mt-4 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('No drafts found') }}</p>
                    <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Draft posts will appear here instead of the calendar once they are saved but not scheduled for delivery yet.') }}</p>
                </div>
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
                @foreach ($draftCards as $draft)
                    @php
                        $hasMedia = filled($draft['media_preview']);
                        $providerChipStyle = publishing_provider_chip_style($draft['provider_key']);
                    @endphp

                    <article class="flex h-full flex-col overflow-hidden rounded-[1.5rem] border shadow-[0_24px_60px_-40px_rgba(15,23,42,0.24)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-base) 97%, transparent);">
                        <div class="flex items-start justify-between gap-3 border-b px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.52);">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full text-xs" style="{{ $providerChipStyle }}">
                                        <i class="{{ $draft['provider_icon'] }}"></i>
                                    </span>
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em]" style="{{ $providerChipStyle }}">
                                        {{ $draft['provider_label'] }}
                                    </span>
                                    @if ($draft['review_status'] !== '')
                                        <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em]" style="{{ $draft['review_status'] === 'pending'
                                            ? 'background-color: rgba(245, 158, 11, 0.14); color: #d97706;'
                                            : ($draft['review_status'] === 'rejected'
                                                ? 'background-color: rgba(239, 68, 68, 0.12); color: #dc2626;'
                                                : 'background-color: rgba(16, 185, 129, 0.14); color: #059669;') }}">
                                            {{ $draft['review_badge'] ?: str((string) $draft['review_status'])->headline() }}
                                        </span>
                                    @endif
                                </div>
                                <p class="mt-3 text-sm font-semibold leading-6" style="color: var(--theme-header-text-color);">{{ $draft['channel'] }}</p>
                                @if ($draft['handle'])
                                    <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ $draft['handle'] }}</p>
                                @endif
                            </div>

                            <div class="text-right text-[11px] leading-5" style="color: var(--theme-muted-text-color);">
                                <p>{{ __('Updated') }}</p>
                                <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ optional($draft['updated_at'])->format('d/m/Y H:i') ?: '--' }}</p>
                            </div>
                        </div>

                        <div class="flex flex-1 flex-col gap-4 px-4 py-4">
                            <div class="flex items-start gap-4">
                                <div class="min-w-0 flex-1">
                                    @if ($draft['caption'] !== '')
                                        <p class="text-sm leading-6" style="color: var(--theme-header-text-color); display: -webkit-box; -webkit-line-clamp: 5; -webkit-box-orient: vertical; overflow: hidden;">
                                            {{ $draft['caption'] }}
                                        </p>
                                    @elseif ($draft['title'])
                                        <p class="text-base font-semibold leading-7" style="color: var(--theme-header-text-color); display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                            {{ $draft['title'] }}
                                        </p>
                                    @else
                                        <p class="text-sm italic leading-6" style="color: var(--theme-muted-text-color);">
                                            {{ __('This draft includes media or publishing settings, but no written caption yet.') }}
                                        </p>
                                    @endif
                                </div>

                                @if ($hasMedia)
                                    <div class="h-[5.75rem] w-[5.75rem] shrink-0 overflow-hidden rounded-[1rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.58);">
                                        <img src="{{ $draft['media_preview'] }}" alt="{{ $draft['title'] ?: $draft['channel'] }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                                    </div>
                                @endif
                            </div>

                            <div class="grid gap-3 sm:grid-cols-3">
                                <div class="rounded-[1rem] border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.52);">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Media') }}</p>
                                    <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $draft['media_type'] }}</p>
                                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ trans_choice(':count file|:count files', $draft['media_count'], ['count' => $draft['media_count']]) }}</p>
                                </div>
                                <div class="rounded-[1rem] border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.52);">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Schedule') }}</p>
                                    <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $draft['scheduled_at'] ? $draft['scheduled_at']->format('d/m/Y H:i') : __('Not scheduled') }}</p>
                                </div>
                                <div class="rounded-[1rem] border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.52);">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Draft id') }}</p>
                                    <p class="mt-2 truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $draft['id'] }}</p>
                                </div>
                            </div>

                            @if ($draft['review_status'] !== '')
                                <div class="rounded-[1rem] border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); background-color: rgba(var(--theme-border-color-rgb), 0.03);">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Approval audit') }}</p>
                                    @if ($draft['review_submitted_at'])
                                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">
                                            {{ __('Submitted') }}
                                            @if ($draft['review_submitted_by'] !== '')
                                                {{ __('by :name', ['name' => $draft['review_submitted_by']]) }}
                                            @endif
                                            {{ __('on :date', ['date' => $draft['review_submitted_at']->format('d/m/Y H:i')]) }}
                                        </p>
                                    @endif
                                    @if ($draft['review_decided_at'])
                                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">
                                            {{ __('Decided') }}
                                            @if ($draft['review_decided_by'] !== '')
                                                {{ __('by :name', ['name' => $draft['review_decided_by']]) }}
                                            @endif
                                            {{ __('on :date', ['date' => $draft['review_decided_at']->format('d/m/Y H:i')]) }}
                                        </p>
                                    @endif
                                    @if ($draft['review_note'] !== '')
                                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-header-text-color);">{{ $draft['review_note'] }}</p>
                                    @endif
                                </div>
                            @endif

                            <div class="mt-auto flex flex-wrap gap-2 pt-1">
                                <x-ui.button :href="route('portal.publishing.calendar', ['edit' => $draft['id']])" variant="primary" size="md" class="flex-1 min-w-[11rem]" wire:navigate>
                                    <i class="fa-light fa-pen-to-square"></i>
                                    {{ __('Edit Draft') }}
                                </x-ui.button>
                                <button
                                    type="button"
                                    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-[0.95rem] border px-4 text-sm font-semibold transition hover:opacity-90"
                                    style="border-color: rgba(239, 68, 68, 0.24); background-color: rgba(239, 68, 68, 0.08); color: #dc2626;"
                                    x-data
                                    x-on:click="if (confirm(@js(__('Delete this draft?')))) { $wire.deleteDraft(@js($draft['id'])) }"
                                >
                                    <i class="fa-light fa-trash-can"></i>
                                    {{ __('Delete') }}
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-5">
                {{ $drafts->links() }}
            </div>
        @endif
    </section>
</div>
