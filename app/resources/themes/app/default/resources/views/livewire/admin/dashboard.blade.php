@php
    $widthClasses = [
        'compact' => 'lg:col-span-4',
        'half' => 'lg:col-span-6',
        'wide' => 'lg:col-span-8',
        'full' => 'lg:col-span-12',
    ];

    $adminUser = auth()->user();
    $adminName = $adminUser?->name ?: __('Administrator');
    $todayLabel = now()->translatedFormat('d M Y');
@endphp

<div
    class="space-y-8 lg:space-y-10"
    x-data="{
        draggingId: null,
        saveTimeout: null,
        saveUrl: @js(route('dashboard.layout.update')),
        csrf: @js(csrf_token()),
        saving: false,
        saved: false,
        startDrag(event) {
            const card = event.currentTarget;
            this.draggingId = card.dataset.dashboardId;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', this.draggingId);
            card.classList.add('opacity-60');
        },
        dragOver(event) {
            const target = event.currentTarget;
            const sourceId = this.draggingId;

            if (!sourceId || sourceId === target.dataset.dashboardId) {
                return;
            }

            const board = this.$refs.board;
            const source = board.querySelector(`[data-dashboard-id='${sourceId}']`);

            if (!source || !target || source === target) {
                return;
            }

            const rect = target.getBoundingClientRect();
            const before = event.clientY < rect.top + rect.height / 2;

            if (before) {
                board.insertBefore(source, target);
            } else {
                board.insertBefore(source, target.nextSibling);
            }
        },
        endDrag(event) {
            event.currentTarget.classList.remove('opacity-60');
            this.draggingId = null;
            this.persist();
        },
        persist() {
            clearTimeout(this.saveTimeout);

            this.saveTimeout = setTimeout(async () => {
                const itemIds = Array.from(this.$refs.board.querySelectorAll('[data-dashboard-id]'))
                    .map((element) => element.dataset.dashboardId);

                this.saving = true;
                this.saved = false;

                await fetch(this.saveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ item_ids: itemIds }),
                });

                this.saving = false;
                this.saved = true;

                setTimeout(() => {
                    this.saved = false;
                }, 1600);
            }, 180);
        },
    }"
>
    <div class="flex items-center justify-end">
        <span
            x-cloak
            x-show="saving || saved"
            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
            style="background: rgba(var(--theme-accent-rgb,37,99,235),0.12); color: var(--theme-accent,#2563eb);"
        >
            <span x-show="saving">{{ __('Saving layout...') }}</span>
            <span x-show="saved">{{ __('Layout saved') }}</span>
        </span>
    </div>

    <section
        class="overflow-hidden rounded-[1.75rem] border p-4 lg:p-6"
        style="border-color: rgba(var(--theme-accent-rgb), 0.14); background:
            radial-gradient(circle at top left, rgba(var(--theme-accent-rgb), 0.12), transparent 28%),
            linear-gradient(180deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.98), rgba(var(--theme-surface-base-rgb,255,255,255),0.94));"
    >
        <div
            class="rounded-[1.45rem] border px-6 py-6 lg:px-7 lg:py-7"
            style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background:
                linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 96%, rgba(var(--theme-accent-rgb),0.03)), color-mix(in srgb, var(--theme-surface-base) 95%, rgba(var(--theme-accent-rgb),0.02)));"
        >
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-4xl space-y-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 text-[11px] font-semibold uppercase tracking-[0.24em]" style="border-color: rgba(var(--theme-accent-rgb), 0.22); background: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-accent);">
                            <i class="fa-light fa-shield-check text-[11px]"></i>
                            {{ __('Admin Dashboard') }}
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 text-[11px] font-semibold uppercase tracking-[0.24em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background: rgba(var(--theme-surface-base-rgb,255,255,255),0.82); color: var(--theme-muted-foreground);">
                            <i class="fa-light fa-calendar-day text-[11px]"></i>
                            {{ $todayLabel }}
                        </span>
                    </div>

                    <div class="space-y-3">
                        <h1 class="text-3xl font-semibold tracking-tight text-[var(--theme-heading-text-color)] lg:text-[2.15rem]">
                            {{ __('Welcome back, :name', ['name' => $adminName]) }}
                        </h1>

                        <p class="max-w-3xl text-base leading-8 text-[var(--theme-paragraph-color)]">
                            {{ __('Review platform health, revenue surfaces, user activity, content operations, and AI workflows from one executive control surface before you dive into module-level actions.') }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 text-sm text-[var(--theme-paragraph-color)]">
                        <span class="inline-flex items-center gap-2 rounded-full border px-4 py-2" style="border-color: rgba(var(--theme-accent-rgb), 0.18); background: rgba(var(--theme-accent-rgb), 0.05);">
                            <i class="fa-light fa-grid-2-plus text-[12px] text-[var(--theme-accent)]"></i>
                            {{ __('Drag widgets to shape the admin workspace') }}
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full border px-4 py-2" style="border-color: rgba(var(--theme-accent-rgb), 0.18); background: rgba(var(--theme-accent-rgb), 0.05);">
                            <i class="fa-light fa-chart-mixed-up-circle-dollar text-[12px] text-[var(--theme-accent)]"></i>
                            {{ __('Use the snapshots below to catch shifts early') }}
                        </span>
                    </div>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-3 lg:justify-end">
                    <x-ui.button :href="route('admin-users.index')" wire:navigate>
                        {{ __('Manage users') }}
                    </x-ui.button>

                    <x-ui.button :href="route('admin-support.index')" variant="outline" wire:navigate>
                        {{ __('Open support') }}
                    </x-ui.button>

                    <x-ui.button :href="route('admin-plans.index')" variant="outline" wire:navigate>
                        {{ __('Review plans') }}
                    </x-ui.button>
                </div>
            </div>
        </div>
    </section>

    @if (($welcomeItems ?? []) !== [])
        <div class="space-y-7 lg:space-y-8">
            @foreach ($welcomeItems as $item)
                <section>
                    {!! $item['content'] !!}
                </section>
            @endforeach
        </div>
    @endif

    @if ($dashboardItems === [])
        <x-ui.empty
            :title="__('No dashboard items registered')"
            :description="__('Start by registering widgets from admin modules with register_admin_dashboard_item().')"
        />
    @else
        <div
            x-ref="board"
            class="grid gap-x-5 gap-y-8 lg:gap-y-10 lg:grid-cols-12"
        >
            @foreach ($dashboardItems as $item)
                <section
                    draggable="true"
                    data-dashboard-id="{{ $item['id'] }}"
                    class="group relative {{ $widthClasses[$item['width'] ?? 'half'] ?? $widthClasses['half'] }}"
                    x-on:dragstart="startDrag($event)"
                    x-on:dragover.prevent="dragOver($event)"
                    x-on:dragend="endDrag($event)"
                >
                    <div class="pointer-events-none absolute right-3 top-3 z-10 inline-flex h-8 w-8 items-center justify-center rounded-full border bg-white/90 text-slate-400 opacity-0 shadow-sm transition group-hover:opacity-100" style="border-color: var(--theme-border-color);">
                        <i class="fa-light fa-grip-dots text-sm"></i>
                    </div>

                    {!! $item['content'] !!}
                </section>
            @endforeach
        </div>
    @endif
</div>
