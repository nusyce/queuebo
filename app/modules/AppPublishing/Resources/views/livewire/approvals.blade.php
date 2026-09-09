<div class="mx-auto flex w-full max-w-[1680px] flex-col gap-6 px-4 py-4 sm:px-5 xl:px-6">
    <section class="overflow-hidden rounded-[1.9rem] border shadow-[0_30px_80px_-50px_rgba(15,23,42,0.28)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        radial-gradient(circle at top left, rgba(245, 158, 11, 0.12), transparent 28%),
        radial-gradient(circle at top right, rgba(var(--theme-accent-rgb), 0.08), transparent 24%),
        color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
        <div class="flex flex-col gap-5 px-5 py-6 lg:flex-row lg:items-start lg:justify-between lg:px-7">
            <div class="flex min-w-0 items-start gap-4">
                <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-[1.35rem] border shadow-[0_24px_54px_-34px_rgba(15,23,42,0.3)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background: linear-gradient(135deg, rgba(245, 158, 11, 0.18), rgba(245, 158, 11, 0.06)); color: #d97706;">
                    <i class="fa-light fa-badge-check text-xl"></i>
                </span>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.26em]" style="color: var(--theme-muted-text-color);">{{ __('Publishing workflow') }}</p>
                    <h1 class="mt-1 text-[1.85rem] font-semibold tracking-[-0.06em] sm:text-[2.1rem]" style="color: var(--theme-header-text-color);">{{ __('Approvals') }}</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-7 sm:text-[15px]" style="color: var(--theme-muted-text-color);">
                        {{ __('Review posts from teammates before they are released to the publishing queue or pushed live immediately.') }}
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
                <x-ui.button :href="route('portal.publishing.drafts')" variant="outline" size="lg" class="shadow-[0_18px_40px_-30px_rgba(15,23,42,0.22)]" wire:navigate>
                    <i class="fa-light fa-file-pen"></i>
                    {{ __('Drafts') }}
                </x-ui.button>
            </div>
        </div>
    </section>

    <section class="rounded-[1.8rem] border px-5 py-5 shadow-[0_24px_60px_-40px_rgba(15,23,42,0.22)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        linear-gradient(180deg, rgba(245, 158, 11, 0.03), transparent 42%),
        color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_24rem_auto] xl:items-end">
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Approval search') }}</p>
                <x-ui.icon-input
                    wire:model.live.debounce.300ms="search"
                    icon="fa-light fa-magnifying-glass"
                    :placeholder="__('Search by title, caption, or post id...')"
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

            <x-ui.button type="button" variant="outline" size="lg" wire:click="clearFilters" class="h-12 shrink-0 shadow-[0_18px_40px_-30px_rgba(15,23,42,0.18)]">
                <i class="fa-light fa-rotate-left mr-2"></i>
                {{ __('Reset') }}
            </x-ui.button>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 2xl:grid-cols-4">
        <div class="rounded-[1.6rem] border px-5 py-5 shadow-[0_24px_60px_-40px_rgba(15,23,42,0.22)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Pending approvals') }}</p>
            <p class="mt-3 text-[2.2rem] font-semibold tracking-[-0.06em]" style="color: var(--theme-header-text-color);">{{ $summary['pending'] }}</p>
            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Approval items still waiting for a reviewer decision.') }}</p>
        </div>
        <div class="rounded-[1.6rem] border px-5 py-5 shadow-[0_24px_60px_-40px_rgba(15,23,42,0.22)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Networks') }}</p>
            <p class="mt-3 text-[2.2rem] font-semibold tracking-[-0.06em]" style="color: var(--theme-header-text-color);">{{ $summary['providers'] }}</p>
            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Providers represented in the current approval inbox.') }}</p>
        </div>
        <div class="rounded-[1.6rem] border px-5 py-5 shadow-[0_24px_60px_-40px_rgba(15,23,42,0.22)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Submitted today') }}</p>
            <p class="mt-3 text-[2.2rem] font-semibold tracking-[-0.06em]" style="color: var(--theme-header-text-color);">{{ $summary['submitted_today'] }}</p>
            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('New approval requests created during the current day.') }}</p>
        </div>
        <div class="rounded-[1.6rem] border px-5 py-5 shadow-[0_24px_60px_-40px_rgba(15,23,42,0.22)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
            linear-gradient(180deg, rgba(245, 158, 11, 0.08), rgba(245, 158, 11, 0.03)),
            color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Oldest pending') }}</p>
            <p class="mt-3 text-[1.45rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ $summary['oldest_pending'] ?: '—' }}</p>
            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('The oldest request still waiting in this inbox.') }}</p>
        </div>
    </section>

    <section class="rounded-[1.9rem] border p-5 shadow-[0_30px_80px_-50px_rgba(15,23,42,0.28)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
        @if ($reviewCards->isEmpty())
            <div class="flex min-h-[18rem] items-center justify-center rounded-[1.4rem] border border-dashed px-6 py-10 text-center" style="border-color: rgba(var(--theme-border-color-rgb), 0.58);">
                <div class="max-w-md">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-[1.15rem]" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.12), rgba(245, 158, 11, 0.04)); color: #d97706;">
                        <i class="fa-light fa-badge-check text-xl"></i>
                    </div>
                    <p class="mt-4 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Approval inbox is clear') }}</p>
                    <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('New publishing items that require approval will appear here once teammates submit them.') }}</p>
                </div>
            </div>
        @else
            <div class="grid gap-4 xl:grid-cols-3">
                @foreach ($reviewCards as $review)
                    @php($providerChipStyle = publishing_provider_chip_style($review['provider_key']))
                    <article x-data="{ expanded: false }" class="overflow-hidden rounded-[1.5rem] border shadow-[0_24px_60px_-40px_rgba(15,23,42,0.24)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-base) 97%, transparent);">
                        <div class="relative border-b" style="border-color: rgba(var(--theme-border-color-rgb), 0.52);">
                            <div class="absolute inset-x-0 top-0 z-10 flex items-start gap-3 p-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full text-xs shadow-sm" style="{{ $providerChipStyle }}">
                                        <i class="{{ $review['provider_icon'] }}"></i>
                                    </span>
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] shadow-sm" style="{{ $providerChipStyle }}">
                                        {{ $review['provider_label'] }}
                                    </span>
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] shadow-sm" style="background-color: rgba(245, 158, 11, 0.14); color: #d97706;">
                                        {{ __('Pending approval') }}
                                    </span>
                                </div>
                            </div>

                            @if ($review['media_preview'])
                                <div class="aspect-[16/10] overflow-hidden" style="background-color: rgba(var(--theme-border-color-rgb), 0.08);">
                                    @if (str_starts_with(strtolower($review['media_mime']), 'video/'))
                                        <video src="{{ $review['media_preview'] }}" muted playsinline preload="metadata" class="h-full w-full object-cover"></video>
                                    @else
                                        <img src="{{ $review['media_preview'] }}" alt="{{ $review['title'] ?: $review['channel'] }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                                    @endif
                                </div>
                            @else
                                @php($placeholderIcon = ($review['caption'] !== '' || $review['title']) ? 'fa-light fa-align-center' : 'fa-light fa-image')
                                <div class="flex aspect-[16/10] items-center justify-center p-5" style="background:
                                    linear-gradient(180deg, rgba(var(--theme-accent-rgb), 0.04), rgba(var(--theme-border-color-rgb), 0.02)),
                                    color-mix(in srgb, var(--theme-surface-base) 98%, transparent);">
                                    <span class="inline-flex h-20 w-20 items-center justify-center rounded-full" style="background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                                        <i class="{{ $placeholderIcon }} text-[2.5rem]"></i>
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="space-y-4 p-4">
                            <div>
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-base font-semibold leading-6" style="color: var(--theme-header-text-color);">{{ $review['channel'] }}</p>
                                        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px]" style="color: var(--theme-muted-text-color);">
                                            @if ($review['handle'])
                                                <span>{{ $review['handle'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    @if ($review['caption'] !== '')
                                        <div
                                            class="whitespace-pre-line text-sm leading-6"
                                            style="color: var(--theme-header-text-color);"
                                            x-bind:class="expanded ? '' : 'max-h-[9rem] overflow-hidden'"
                                        >
                                            {{ $review['caption'] }}
                                        </div>
                                        @if (mb_strlen($review['caption']) > 220)
                                            <button
                                                type="button"
                                                class="mt-2 text-xs font-semibold transition hover:opacity-80"
                                                style="color: var(--theme-accent);"
                                                x-on:click="expanded = !expanded"
                                                x-text="expanded ? @js(__('Show less')) : @js(__('Show more'))"
                                            ></button>
                                        @endif
                                    @elseif ($review['title'])
                                        <p class="text-sm leading-6" style="color: var(--theme-header-text-color);">
                                            {{ $review['title'] }}
                                        </p>
                                    @else
                                        <p class="text-sm italic leading-6" style="color: var(--theme-muted-text-color);">
                                            {{ __('This approval request includes media and publishing settings, but no written caption yet.') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="rounded-[1rem] border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.52);">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('When approved') }}</p>
                                    <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">
                                        {{ $review['schedule_mode'] === 'immediately' ? __('Publish immediately') : __('Return to schedule') }}
                                    </p>
                                </div>
                                <div class="rounded-[1rem] border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.52);">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Scheduled time') }}</p>
                                    <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $review['scheduled_at'] ? $review['scheduled_at']->format('d/m/Y H:i') : __('Not scheduled') }}</p>
                                </div>
                            </div>

                            @if ($review['internal_note'] !== '')
                                <div class="rounded-[1rem] border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); background-color: rgba(var(--theme-border-color-rgb), 0.03);">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Internal note') }}</p>
                                    <p class="mt-2 text-sm leading-6" style="color: var(--theme-header-text-color);">{{ $review['internal_note'] }}</p>
                                </div>
                            @endif

                            <x-ui.textarea
                                wire:model.defer="reviewNotes.{{ $review['review_id'] }}"
                                :label="__('Decision note')"
                                rows="3"
                                :placeholder="__('Explain why this post is approved or what needs to change before it can be published.')"
                            ></x-ui.textarea>

                            <div class="space-y-2 pt-1">
                                <x-ui.button :href="route('portal.publishing.calendar', ['edit' => $review['post_id']])" variant="outline" size="md" class="flex w-full justify-center [&_i]:!text-current" wire:navigate>
                                    <i class="fa-light fa-pen-to-square"></i>
                                    {{ __('Open in composer') }}
                                </x-ui.button>
                                <div class="grid grid-cols-2 gap-2">
                                    <x-ui.button type="button" variant="primary" size="md" class="flex w-full justify-center [&_i]:!text-current" wire:click="approveReview({{ $review['review_id'] }})">
                                        <i class="fa-light fa-badge-check"></i>
                                        {{ __('Approve') }}
                                    </x-ui.button>
                                    <button
                                        type="button"
                                        class="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-[0.95rem] border px-4 text-sm font-semibold transition hover:opacity-90"
                                        style="border-color: rgba(239, 68, 68, 0.24); background-color: rgba(239, 68, 68, 0.08); color: #dc2626;"
                                        wire:click="rejectReview({{ $review['review_id'] }})"
                                    >
                                        <i class="fa-light fa-ban" style="color: currentColor;"></i>
                                        {{ __('Reject') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-5">
                {{ $reviews->links() }}
            </div>
        @endif
    </section>
</div>
