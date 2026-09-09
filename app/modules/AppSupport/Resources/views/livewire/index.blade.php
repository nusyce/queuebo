<div class="mx-auto max-w-[88rem] space-y-6">
    @if ($statusMessage)
        <x-ui.alert :title="__('Saved')" :description="$statusMessage" variant="success" dismissible />
    @endif

    <x-ui.page-hero
        :eyebrow="__('User portal')"
        :title="__('Support')"
        :description="__('Open support tickets, review replies from the admin team, and keep your conversation history in one place.')"
        :count="$summary['total']"
        icon="fa-light fa-headset"
    >
        <x-slot:actions>
            <x-ui.modal width="lg" :title="__('New support ticket')" :description="__('Describe your issue clearly so the team can respond with the right context.')">
                <x-slot:trigger>
                    <x-ui.button type="button">
                        <i class="fa-light fa-plus"></i>
                        {{ __('Create ticket') }}
                    </x-ui.button>
                </x-slot:trigger>

                <form id="portal-support-create-form" wire:submit="createTicket" class="space-y-5">
                    <x-ui.select wire:model.defer="cateId" name="cateId" :label="__('Category')" :error="$errors->first('cateId')">
                        <option value="">{{ __('Select category') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </x-ui.select>

                    <x-ui.input wire:model.defer="title" name="title" :label="__('Subject')" :error="$errors->first('title')" :placeholder="__('Brief summary of the issue')" required />
                    <x-ui.textarea wire:model.defer="content" name="content" :label="__('Details')" :error="$errors->first('content')" rows="6" :placeholder="__('Explain what happened, what you expected, and any steps to reproduce the issue.')" />
                </form>

                <x-slot:footer>
                    <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                    <x-ui.button type="submit" form="portal-support-create-form">{{ __('Create ticket') }}</x-ui.button>
                </x-slot:footer>
            </x-ui.modal>
        </x-slot:actions>
    </x-ui.page-hero>

    <x-ui.metric-strip
        :items="[
            ['label' => __('Total'), 'value' => number_format($summary['total']), 'description' => __('All support tickets created from your account.'), 'progress' => 100, 'tone' => 'var(--theme-accent)', 'icon' => 'fa-light fa-folders', 'iconSurface' => 'color-mix(in srgb, var(--theme-accent) 10%, transparent)', 'iconBorder' => 'color-mix(in srgb, var(--theme-accent) 18%, var(--theme-border-color))'],
            ['label' => __('Open'), 'value' => number_format($summary['open']), 'description' => __('Tickets that are still waiting for action or a final answer.'), 'progress' => $summary['total'] > 0 ? (int) round(($summary['open'] / $summary['total']) * 100) : 0, 'tone' => 'var(--theme-accent)', 'icon' => 'fa-light fa-door-open', 'iconSurface' => 'color-mix(in srgb, var(--theme-accent) 10%, transparent)', 'iconBorder' => 'color-mix(in srgb, var(--theme-accent) 18%, var(--theme-border-color))'],
            ['label' => __('Resolved'), 'value' => number_format($summary['resolved']), 'description' => __('Tickets already completed by the support team.'), 'progress' => $summary['total'] > 0 ? (int) round(($summary['resolved'] / $summary['total']) * 100) : 0, 'tone' => 'var(--theme-success-color)', 'icon' => 'fa-light fa-circle-check', 'iconSurface' => 'color-mix(in srgb, var(--theme-success-color) 10%, transparent)', 'iconBorder' => 'color-mix(in srgb, var(--theme-success-color) 18%, var(--theme-border-color))'],
            ['label' => __('Unread'), 'value' => number_format($summary['unread']), 'description' => __('Threads with new admin replies you have not opened yet.'), 'progress' => $summary['total'] > 0 ? (int) round(($summary['unread'] / $summary['total']) * 100) : 0, 'tone' => 'var(--theme-warning-color)', 'icon' => 'fa-light fa-bell', 'iconSurface' => 'color-mix(in srgb, var(--theme-warning-color) 10%, transparent)', 'iconBorder' => 'color-mix(in srgb, var(--theme-warning-color) 18%, var(--theme-border-color))'],
        ]"
        columns="md:grid-cols-2 xl:grid-cols-4"
    />

    <form method="GET" action="{{ route('portal.support.index') }}" class="space-y-4">
        <x-ui.filter-panel fields-class="xl:grid-cols-[minmax(0,1.35fr)_240px_auto]">
            <x-slot:fields>
                <x-ui.input name="q" :label="__('Search')" :value="$filters['q']" :placeholder="__('Ticket id, subject, content...')" />
                <x-ui.select name="status" :label="__('Status')">
                    <option value="all" @selected($filters['status'] === 'all')>{{ __('All') }}</option>
                    <option value="1" @selected((string) $filters['status'] === '1')>{{ __('Open') }}</option>
                    <option value="2" @selected((string) $filters['status'] === '2')>{{ __('Resolved') }}</option>
                    <option value="0" @selected((string) $filters['status'] === '0')>{{ __('Closed') }}</option>
                </x-ui.select>
            </x-slot:fields>
            <x-slot:actions>
                <x-ui.button type="submit">{{ __('Filter') }}</x-ui.button>
                <x-ui.button href="{{ route('portal.support.index') }}" variant="outline" wire:navigate>{{ __('Reset') }}</x-ui.button>
            </x-slot:actions>
            <x-slot:chips>
                @if ($filters['status'] !== 'all')
                    <x-ui.badge variant="neutral">{{ __('Status') }}: {{ $filters['status'] === '1' ? __('Open') : ($filters['status'] === '2' ? __('Resolved') : __('Closed')) }}</x-ui.badge>
                @endif
            </x-slot:chips>
        </x-ui.filter-panel>
    </form>

    <x-ui.datatable-shell
        :title="__('Your tickets')"
        :info="__('Support requests opened from this account.')"
        header-class="py-4"
        eyebrow-class="text-[10px] tracking-[0.2em]"
        title-class="mt-1 text-[1.15rem] tracking-[-0.025em]"
        description-class="mt-1 leading-6"
    >
        <x-slot:toolbar>
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.badge variant="primary">{{ number_format($summary['total']) }} {{ __('tickets') }}</x-ui.badge>
                <x-ui.badge variant="success">{{ number_format($summary['open']) }} {{ __('open') }}</x-ui.badge>
                @if ($summary['unread'] > 0)
                    <x-ui.badge variant="danger">{{ number_format($summary['unread']) }} {{ __('new updates') }}</x-ui.badge>
                @endif
            </div>
        </x-slot:toolbar>

        <x-ui.table class="rounded-none border-0 shadow-none">
            <x-ui.table-head>
                <x-ui.table-cell head>{{ __('Ticket') }}</x-ui.table-cell>
                <x-ui.table-cell head>{{ __('Queue') }}</x-ui.table-cell>
                <x-ui.table-cell head>{{ __('Updated') }}</x-ui.table-cell>
                <x-ui.table-cell head>{{ __('Actions') }}</x-ui.table-cell>
            </x-ui.table-head>
            <x-ui.table-body>
                @forelse ($tickets as $ticket)
                    <x-ui.table-row>
                        <x-ui.table-cell>
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-[1rem] font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ $ticket->title }}</p>
                                    @if ($ticket->user_read)
                                        <x-ui.badge variant="danger" class="px-2 py-0.5 text-[10px] tracking-[0.16em]">{{ __('Unread') }}</x-ui.badge>
                                    @endif
                                </div>

                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs" style="color: var(--theme-muted-text-color);">
                                    <span class="font-mono">{{ $ticket->id_secure }}</span>
                                    <span class="inline-flex h-1 w-1 rounded-full" style="background-color: color-mix(in srgb, var(--theme-muted-text-color) 38%, transparent);"></span>
                                    <span>{{ $ticket->comments_count }} {{ __('replies') }}</span>
                                </div>

                                <p class="max-w-[42rem] text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $ticket->excerpt() }}</p>
                            </div>
                        </x-ui.table-cell>
                        <x-ui.table-cell>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold" style="border-color: color-mix(in srgb, {{ $ticket->category?->color ?: 'var(--theme-accent)' }} 18%, var(--theme-border-color)); color: {{ $ticket->category?->color ?: 'var(--theme-accent)' }}; background-color: color-mix(in srgb, {{ $ticket->category?->color ?: 'var(--theme-accent)' }} 8%, transparent);">
                                    @if ($ticket->category?->icon)
                                        <i class="{{ $ticket->category->icon }}"></i>
                                    @endif
                                    {{ $ticket->category?->name ?: __('Unassigned') }}
                                </span>
                                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold" style="border-color: color-mix(in srgb, {{ $ticket->type?->color ?: 'var(--theme-border-color)' }} 18%, var(--theme-border-color)); color: {{ $ticket->type?->color ?: 'var(--theme-muted-text-color)' }}; background-color: color-mix(in srgb, {{ $ticket->type?->color ?: 'var(--theme-border-color)' }} 7%, transparent);">
                                    @if ($ticket->type?->icon)
                                        <i class="{{ $ticket->type->icon }}"></i>
                                    @endif
                                    {{ $ticket->type?->name ?: __('No type') }}
                                </span>
                                <x-ui.badge :variant="$ticket->statusVariant()">{{ $ticket->statusLabel() }}</x-ui.badge>
                            </div>
                        </x-ui.table-cell>
                        <x-ui.table-cell>
                            <div class="space-y-1">
                                <p class="text-sm font-medium" style="color: var(--theme-header-text-color);">{{ $ticket->changedAtFormatted() ?: __('N/A') }}</p>
                                <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ $ticket->changedFromHuman() ?: __('No activity') }}</p>
                            </div>
                        </x-ui.table-cell>
                        <x-ui.table-cell>
                            <div class="flex flex-wrap items-center gap-2 justify-end">
                                <x-ui.button href="{{ route('portal.support.show', $ticket) }}" variant="outline" size="sm" wire:navigate>{{ __('View') }}</x-ui.button>
                                @if ((int) $ticket->status === 1)
                                    <form wire:submit="resolveTicket({{ $ticket->id }})">
                                        <x-ui.button type="submit" size="sm">{{ __('Resolve') }}</x-ui.button>
                                    </form>
                                @endif
                            </div>
                        </x-ui.table-cell>
                    </x-ui.table-row>
                @empty
                    <x-ui.table-row>
                        <x-ui.table-cell colspan="4" class="py-10"><x-ui.empty icon="fa-light fa-headset" :title="__('No support tickets found.')" :description="__('Create your first ticket to start a support thread with the admin team.')" /></x-ui.table-cell>
                    </x-ui.table-row>
                @endforelse
            </x-ui.table-body>
        </x-ui.table>

        @if ($tickets->hasPages())
            <div class="border-t px-6 py-4" style="border-color: var(--theme-border-color);">{{ $tickets->links() }}</div>
        @endif
    </x-ui.datatable-shell>
</div>
