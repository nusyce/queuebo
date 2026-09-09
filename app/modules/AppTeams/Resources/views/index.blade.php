@php
    $team = $teamModel;
    $memberCount = $team?->members?->count() ?? 0;
    $inviteCount = $pendingInvites->count();
    $reviewCount = $reviews->where('status', 'pending')->count();
    $conversationCount = $conversations->count();
    $conversationStreamPayload = [
        'teamId' => (int) ($team?->id ?? 0),
        'activeConversationId' => (int) ($activeConversation?->id ?? 0),
        'conversations' => $conversations->map(fn ($conversation) => [
            'id' => (int) $conversation->id,
            'title' => $conversation->title ?: __('Untitled room'),
            'messages_count' => (int) $conversation->messages_count,
            'messages_label' => number_format((int) $conversation->messages_count).' '.__('messages'),
            'last_message_label' => $conversation->last_message_at?->diffForHumans() ?: __('new'),
            'href' => $team ? route('portal.teams', ['team' => $team->id, 'conversation' => $conversation->id]) : '#',
            'active' => (int) ($activeConversation?->id ?? 0) === (int) $conversation->id,
        ])->values()->all(),
        'activeConversation' => $activeConversation ? [
            'id' => (int) $activeConversation->id,
            'title' => $activeConversation->title ?: __('Untitled room'),
            'description' => $activeConversation->description,
            'participants_label' => $activeConversation->participants->pluck('name')->join(', '),
            'messages' => $activeConversation->messages->map(fn ($message) => [
                'id' => (int) $message->id,
                'author_name' => $message->author?->name ?: __('Unknown'),
                'body' => (string) $message->body,
                'sent_label' => $message->sent_at?->diffForHumans() ?: $message->created_at?->diffForHumans(),
            ])->values()->all(),
        ] : null,
    ];
@endphp
<div class="space-y-6 px-4 pb-6 pt-4 sm:px-5 xl:px-6">
    <section class="overflow-hidden rounded-[1.6rem] border px-5 py-5 sm:px-6 xl:px-7" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        radial-gradient(circle at top right, rgba(59,130,246,0.10), transparent 32%),
        radial-gradient(circle at left center, rgba(16,185,129,0.08), transparent 26%),
        color-mix(in srgb, var(--theme-surface-overlay) 96%, transparent);">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.6); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 72%, transparent);">
                    <span class="inline-flex h-2 w-2 rounded-full" style="background-color: var(--theme-accent);"></span>
                    {{ __('Team workspace') }}
                </div>
                <h1 class="mt-4 text-[1.9rem] font-semibold tracking-[-0.05em] sm:text-[2.2rem]" style="color: var(--theme-header-text-color);">{{ $team?->name ?: __('Join a team') }}</h1>
                <p class="mt-3 max-w-2xl text-sm leading-7 sm:text-[0.98rem]" style="color: var(--theme-muted-text-color);">
                    {{ $team
                        ? __('Manage roles, invites, post approvals, and collaboration for your publishing team from one workspace.')
                        : __('Redeem an invite code to join your team workspace and collaborate on approvals, discussion, and scheduled posts.') }}
                </p>
            </div>

            @if ($team && $canManageInvites)
                <div class="flex w-full flex-col gap-3 xl:max-w-sm xl:items-end">
                    <x-ui.modal
                        width="lg"
                        open-event="team-invite-modal-open"
                        close-event="team-invite-modal-close"
                        :initially-open="$errors->hasAny(['email', 'message', 'expires_at', 'invite_permissions', 'inviteManagedAccounts'])"
                        :title="__('Invite member')"
                        :description="__('Create a workspace invite by email or generate a code-based invitation with an expiry date.')"
                        body-class="overflow-visible max-h-none px-6 py-6"
                    >
                        <x-slot:trigger>
                            <x-ui.button type="button">
                                <i class="fa-light fa-user-plus mr-2"></i>{{ __('Invite member') }}
                            </x-ui.button>
                        </x-slot:trigger>

                        <form id="team-invite-modal-form" wire:submit="createInvite" class="space-y-5">
                            <x-ui.input wire:model="email" name="email" :label="__('Email address')" :placeholder="__('member@company.com')" :error="$errors->first('email')" />

                            <x-ui.textarea wire:model="message" name="message" :label="__('Invite message')" :error="$errors->first('message')" rows="3">{{ $message }}</x-ui.textarea>

                            <x-ui.field :label="__('Permissions')" :help="__('Choose what this invited member will be allowed to access after joining.')">
                                <div class="grid gap-3 md:grid-cols-2">
                                    @foreach ($teamPermissionLabels as $permissionKey => $permissionLabel)
                                        <x-ui.checkbox
                                            wire:model="invite_permissions"
                                            :value="$permissionKey"
                                            :label="$permissionLabel"
                                        />
                                    @endforeach
                                </div>
                                @if ($errors->first('invite_permissions'))
                                    <p class="mt-2 text-sm font-medium" style="color: var(--theme-danger-color);">{{ $errors->first('invite_permissions') }}</p>
                                @endif
                            </x-ui.field>

                            @if (in_array('channels', $enabled_modules, true) && $workspaceAccounts->isNotEmpty())
                                <x-ui.field :label="__('Managed accounts')" :help="__('Choose which channel accounts this invited member will be allowed to view and manage after joining.')">
                                    <div class="grid gap-3 md:grid-cols-2">
                                        @foreach ($workspaceAccounts as $workspaceAccount)
                                            <x-ui.checkbox
                                                wire:model.live="inviteManagedAccounts"
                                                data-no-loading
                                                :value="$workspaceAccount->id"
                                                :label="$workspaceAccount->display_name ?: (($workspaceAccount->username ? '@'.$workspaceAccount->username.' ' : '').str($workspaceAccount->provider_key)->headline())"
                                            />
                                        @endforeach
                                    </div>
                                    @if ($errors->first('inviteManagedAccounts'))
                                        <p class="mt-2 text-sm font-medium" style="color: var(--theme-danger-color);">{{ $errors->first('inviteManagedAccounts') }}</p>
                                    @endif
                                </x-ui.field>
                            @endif

                            <x-ui.date-picker wire:model="expires_at" name="expires_at" :label="__('Expires on')" :value="$expires_at" :error="$errors->first('expires_at')" />
                        </form>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-3">
                                <x-ui.button type="button" variant="outline" x-on:click="$dispatch('team-invite-modal-close')">{{ __('Cancel') }}</x-ui.button>
                                <x-ui.button type="submit" form="team-invite-modal-form">{{ __('Create invite') }}</x-ui.button>
                            </div>
                        </x-slot:footer>
                    </x-ui.modal>
                </div>
            @endif
        </div>
    </section>

    @if ($errorMessage)
        <x-ui.alert inline variant="danger" :description="$errorMessage" />
    @endif

    @if (! $team)
        <section class="grid gap-4 xl:grid-cols-[28rem_minmax(0,1fr)]">
            <div class="rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Redeem invite code') }}</p>
                </div>
                <form wire:submit="redeemInvite" class="space-y-4 px-5 py-5">
                    <x-ui.input wire:model="invite_code" name="invite_code" :label="__('Invite code')" :error="$errors->first('invite_code')" :placeholder="__('Enter 8-character code')" />
                    <x-ui.button type="submit">{{ __('Join team') }}</x-ui.button>
                </form>
            </div>

            <div class="rounded-[1.25rem] border px-6 py-8" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                <x-ui.empty
                    icon="fa-light fa-user-group"
                    :title="__('No Team Assigned Yet')"
                    :description="__('Your account is not attached to a workspace yet. Ask the team owner for an invite email or a join code, then redeem it here.')"
                />
            </div>
        </section>
    @else
        @php($isMemberLayout = ! in_array((string) $currentUserTeamRole, ['owner', 'admin'], true))
        @if (! $isMemberLayout)
        <div class="grid gap-4 sm:grid-cols-2 {{ ($canManageInvites || ($publishingEnabled && $canApprovePosts) || $chatEnabled) ? 'xl:grid-cols-4' : 'xl:grid-cols-2' }}">
            <div class="rounded-[1.15rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 96%, transparent);">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Members') }}</p>
                <p class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($memberCount) }}</p>
            </div>
            @if ($canManageInvites)
                <div class="rounded-[1.15rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(59,130,246,0.05);">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Pending invites') }}</p>
                    <p class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($inviteCount) }}</p>
                </div>
            @endif
            @if ($publishingEnabled && $canApprovePosts)
                <div class="rounded-[1.15rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(245,158,11,0.05);">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Awaiting approval') }}</p>
                    <p class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($reviewCount) }}</p>
                </div>
            @endif
            @if ($chatEnabled)
                <div class="rounded-[1.15rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(16,185,129,0.05);">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Conversations') }}</p>
                    <p class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($conversationCount) }}</p>
                </div>
            @endif
        </div>

        <div
            x-data="{
                teamsTab: @js($teams_tab ?? 'overview'),
                teamId: @js((int) ($team?->id ?? 0)),
                unreadChatCount: 0,
                conversationMessageCounts: {},
                availableTabs: ['overview', 'members', 'chat', 'approvals'],
                init() {
                    const urlTab = new URL(window.location.href).searchParams.get('teams_tab');
                    if (this.availableTabs.includes(urlTab)) {
                        this.teamsTab = urlTab;
                    }

                    window.addEventListener('team-chat-snapshot', (event) => {
                        const detail = event.detail || {};

                        if (Number(detail.teamId || 0) !== this.teamId) {
                            return;
                        }

                        const conversations = Array.isArray(detail.conversations) ? detail.conversations : [];
                        const nextCounts = {};
                        let newMessageCount = 0;

                        conversations.forEach((conversation) => {
                            const conversationId = Number(conversation?.id || 0);
                            const messageCount = Number(conversation?.messages_count || 0);
                            const previousCount = Number(this.conversationMessageCounts[conversationId] || 0);

                            nextCounts[conversationId] = messageCount;

                            if (previousCount > 0 && messageCount > previousCount) {
                                const isActiveConversation = Number(detail.activeConversationId || 0) === conversationId;

                                if (this.teamsTab !== 'chat' || !isActiveConversation) {
                                    newMessageCount += (messageCount - previousCount);
                                }
                            }
                        });

                        this.conversationMessageCounts = nextCounts;

                        if (newMessageCount > 0) {
                            this.unreadChatCount += newMessageCount;
                        }
                    });
                },
                setTeamsTab(tab) {
                    if (! this.availableTabs.includes(tab)) {
                        tab = 'overview';
                    }

                    this.teamsTab = tab;

                    if (tab === 'chat') {
                        this.unreadChatCount = 0;
                    }

                    const url = new URL(window.location.href);

                    if (tab === 'overview') {
                        url.searchParams.delete('teams_tab');
                    } else {
                        url.searchParams.set('teams_tab', tab);
                    }

                    window.history.replaceState({}, '', url);
                }
            }"
            class="space-y-4"
        >
            <div class="flex flex-wrap gap-2">
                <button type="button" x-on:click="setTeamsTab('overview')" class="rounded-full border px-4 py-2 text-sm font-semibold transition" :style="teamsTab === 'overview' ? 'border-color: rgba(59,130,246,0.28); background-color: rgba(59,130,246,0.10); color: var(--theme-header-text-color);' : 'border-color: rgba(var(--theme-border-color-rgb), 0.6); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent); color: var(--theme-muted-text-color);'">{{ __('Overview') }}</button>
                <button type="button" x-on:click="setTeamsTab('members')" class="rounded-full border px-4 py-2 text-sm font-semibold transition" :style="teamsTab === 'members' ? 'border-color: rgba(59,130,246,0.28); background-color: rgba(59,130,246,0.10); color: var(--theme-header-text-color);' : 'border-color: rgba(var(--theme-border-color-rgb), 0.6); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent); color: var(--theme-muted-text-color);'">{{ __('Members') }}</button>
                @if ($chatEnabled)
                    <button type="button" x-on:click="setTeamsTab('chat')" class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold transition" :style="teamsTab === 'chat' ? 'border-color: rgba(16,185,129,0.28); background-color: rgba(16,185,129,0.10); color: var(--theme-header-text-color);' : 'border-color: rgba(var(--theme-border-color-rgb), 0.6); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent); color: var(--theme-muted-text-color);'">
                        <span>{{ __('Chat') }}</span>
                        <span x-cloak x-show="unreadChatCount > 0" x-text="unreadChatCount > 99 ? '99+' : unreadChatCount" class="inline-flex min-w-[1.2rem] items-center justify-center rounded-full px-1.5 py-0.5 text-[11px] font-semibold leading-none" style="background-color: rgba(225,29,72,0.14); color: rgb(225,29,72);"></span>
                    </button>
                @endif
                @if ($publishingEnabled)
                    <button type="button" x-on:click="setTeamsTab('approvals')" class="rounded-full border px-4 py-2 text-sm font-semibold transition" :style="teamsTab === 'approvals' ? 'border-color: rgba(245,158,11,0.28); background-color: rgba(245,158,11,0.10); color: var(--theme-header-text-color);' : 'border-color: rgba(var(--theme-border-color-rgb), 0.6); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent); color: var(--theme-muted-text-color);'">{{ __('Approvals') }}</button>
                @endif
            </div>

        <div
            class="grid gap-4 xl:grid-cols-[24rem_minmax(0,1fr)]"
            :style="teamsTab === 'overview' ? '' : 'grid-template-columns: minmax(0,1fr);'"
        >
            <section class="space-y-4" x-show="teamsTab === 'overview'" x-cloak>
                <div
                    class="rounded-[1.25rem] border"
                    style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);"
                >
                    <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Team profile') }}</p>
                    </div>
                    @if ($canManageTeam)
                        <form wire:submit.prevent="saveTeam" class="space-y-4 px-5 py-5" data-no-loading>
                            <div class="space-y-2.5" data-no-loading>
                                <x-ui.label>{{ __('Team name') }}</x-ui.label>
                                <input
                                    type="text"
                                    name="team_name"
                                    wire:model.defer="team_name"
                                    data-no-loading
                                    class="flex h-11 w-full border px-4 text-sm font-medium shadow-[0_1px_2px_rgba(15,23,42,0.04)] outline-none transition duration-200 placeholder:font-normal placeholder:text-[var(--theme-input-placeholder)] focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]"
                                    style="border-radius: var(--theme-input-radius, 0.75rem); border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);"
                                >
                                @if ($errors->first('team_name'))
                                    <p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $errors->first('team_name') }}</p>
                                @endif
                            </div>

                            <div class="space-y-2.5" data-no-loading>
                                <x-ui.label>{{ __('Description') }}</x-ui.label>
                                <textarea
                                    name="team_description"
                                    wire:model.defer="team_description"
                                    rows="4"
                                    data-no-loading
                                    class="flex min-h-32 w-full border px-4 py-3.5 text-sm leading-6 shadow-[0_1px_2px_rgba(15,23,42,0.04)] outline-none transition duration-200 placeholder:text-[var(--theme-input-placeholder)] focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]"
                                    style="border-radius: var(--theme-input-radius, 0.75rem); border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);"
                                ></textarea>
                                @if ($errors->first('team_description'))
                                    <p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $errors->first('team_description') }}</p>
                                @endif
                            </div>

                            <div class="space-y-2.5" data-no-loading>
                                <x-ui.label>{{ __('Enabled modules') }}</x-ui.label>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    @foreach ($teamModuleLabels as $moduleKey => $moduleLabel)
                                        @php($checkboxId = 'team-module-'.$moduleKey)
                                        <label for="{{ $checkboxId }}" class="group inline-flex cursor-pointer items-start gap-3" data-no-loading>
                                            <input
                                                id="{{ $checkboxId }}"
                                                type="checkbox"
                                                value="{{ $moduleKey }}"
                                                wire:model.defer="enabled_modules"
                                                data-no-loading
                                                class="peer sr-only"
                                            >
                                            <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-[0.4rem] border border-slate-300 bg-white text-white shadow-[0_1px_2px_rgba(15,23,42,0.06)] transition-all duration-150 group-hover:border-[color:rgba(var(--theme-accent-rgb),0.45)] peer-checked:border-[color:var(--theme-accent)] peer-checked:bg-[color:var(--theme-accent)] peer-checked:text-white peer-checked:shadow-[0_0_0_3px_rgba(var(--theme-accent-rgb),0.14)] peer-checked:[&_svg]:opacity-100">
                                                <svg viewBox="0 0 16 16" aria-hidden="true" class="h-3.5 w-3.5 opacity-0 transition-opacity duration-150" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2">
                                                    <path d="M3.5 8.5 6.5 11.5 12.5 4.5" />
                                                </svg>
                                            </span>
                                            <span class="min-w-0 pt-0.5">
                                                <span class="block text-sm font-medium" style="color: var(--theme-header-text-color);">{{ $moduleLabel }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Choose which modules this workspace should expose to members.') }}</p>
                                @if ($errors->first('enabled_modules'))
                                    <p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $errors->first('enabled_modules') }}</p>
                                @elseif ($errors->first('enabled_modules.*'))
                                    <p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $errors->first('enabled_modules.*') }}</p>
                                @endif
                            </div>

                            <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="saveTeam">{{ __('Save team') }}</x-ui.button>
                        </form>
                    @else
                        <div class="space-y-4 px-5 py-5">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Team name') }}</p>
                                <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $team->name }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Description') }}</p>
                                <p class="mt-2 text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ $team->description ?: __('No team description yet.') }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Enabled modules') }}</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($enabled_modules as $moduleKey)
                                        <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em]" style="background-color: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-header-text-color);">
                                            {{ $teamModuleLabels[$moduleKey] ?? str($moduleKey)->headline() }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                @if (($accessibleTeams ?? collect())->count() > 1)
                    <form wire:submit="switchTeam" class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Switch team') }}</p>
                                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Jump between owned teams and joined workspaces from here.') }}</p>
                            </div>
                            <span class="rounded-full px-2.5 py-1 text-[11px] font-medium" style="background-color: rgba(59,130,246,0.09); color: var(--theme-header-text-color);">
                                {{ number_format(($accessibleTeams ?? collect())->count()) }} {{ __('teams') }}
                            </span>
                        </div>
                        <div class="mt-4">
                            <x-ui.select wire:model="team" name="team" :label="__('Workspace')">
                                @foreach ($accessibleTeams as $workspaceTeam)
                                    <option value="{{ $workspaceTeam->id }}" @selected((int) ($team?->id ?? 0) === (int) $workspaceTeam->id)>
                                        {{ $workspaceTeam->name }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <x-ui.button type="submit" variant="outline">{{ __('Open') }}</x-ui.button>
                        </div>
                    </form>
                @endif

            </section>

            <section class="space-y-4">
                <div
                    class="rounded-[1.25rem] border"
                    style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);"
                    x-show="teamsTab === 'members'"
                    x-cloak
                >
                    <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Members') }}</p>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Set workspace roles for owners, admins, editors, and members.') }}</p>
                    </div>
                    <div class="divide-y" style="divide-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        @foreach ($team->members as $member)
                            <div
                                class="space-y-4 px-5 py-5"
                            >
                                <div class="min-w-0">
                                    <div>
                                        @php($managedAccountIds = collect($memberManagedAccounts[$member->id] ?? [])->map(fn ($id) => (string) $id)->values())
                                        @php($managedAccounts = $workspaceAccounts->filter(fn ($account) => $managedAccountIds->contains((string) $account->id))->values())
                                        @php($managedAccountsPreview = $managedAccounts->take(3))
                                        @php($managedAccountsRemaining = max(0, $managedAccounts->count() - $managedAccountsPreview->count()))
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="truncate text-base font-semibold" style="color: var(--theme-header-text-color);">{{ $member->name }}</p>
                                            <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em]" style="background-color: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-header-text-color);">
                                                {{ __(str(($member->pivot->role ?? 'member'))->headline()->toString()) }}
                                            </span>
                                            @if ($team->owner_user_id === $member->id)
                                                <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em]" style="background-color: rgba(16,185,129,0.10); color: var(--theme-header-text-color);">
                                                    {{ __('Owner') }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ '@'.$member->username }} | {{ $member->email }}</p>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @foreach (($memberPermissions[$member->id] ?? []) as $permission)
                                                <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em]" style="background-color: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-header-text-color);">
                                                    {{ $teamPermissionLabels[$permission] ?? str($permission)->headline() }}
                                                </span>
                                            @endforeach
                                        </div>
                                        @if (in_array('channels', $enabled_modules, true) && $workspaceAccounts->isNotEmpty())
                                            <div class="mt-3 rounded-[0.95rem] border px-3 py-2.5" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); background-color: rgba(var(--theme-border-color-rgb), 0.03);">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full" style="background-color: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-muted-text-color);">
                                                        <i class="fa-light fa-rectangle-history-circle-user text-[12px]"></i>
                                                    </span>
                                                    <span class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Managed accounts') }}</span>
                                                    @if ($team->owner_user_id === $member->id)
                                                        <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em]" style="background-color: rgba(16,185,129,0.10); color: #047857;">
                                                            {{ __('All accounts') }}
                                                        </span>
                                                    @elseif ($managedAccounts->isNotEmpty())
                                                        @foreach ($managedAccountsPreview as $managedAccount)
                                                            <span class="rounded-full px-2.5 py-1 text-[10px] font-medium" style="background-color: rgba(var(--theme-accent-rgb), 0.06); color: var(--theme-header-text-color);">
                                                                {{ $managedAccount->display_name ?: (($managedAccount->username ? '@'.$managedAccount->username.' ' : '').str($managedAccount->provider_key)->headline()) }}
                                                            </span>
                                                        @endforeach
                                                        @if ($managedAccountsRemaining > 0)
                                                            <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em]" style="background-color: rgba(var(--theme-border-color-rgb), 0.08); color: var(--theme-muted-text-color);">
                                                                {{ __('+:count more', ['count' => $managedAccountsRemaining]) }}
                                                            </span>
                                                        @endif
                                                    @else
                                                        <span class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('No accounts assigned') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                @if ($canManageMembers)
                                    <div class="flex flex-wrap gap-3">
                                        <x-ui.modal
                                            width="lg"
                                            :title="__('Permissions')"
                                            :description="$team->owner_user_id === $member->id ? __('Owner has full workspace access.') : __('Choose what this member can manage inside the workspace.')"
                                            body-class="overflow-visible max-h-none px-6 py-6"
                                        >
                                            <x-slot:trigger>
                                                <x-ui.button type="button" size="sm" variant="outline">{{ __('Edit permissions') }}</x-ui.button>
                                            </x-slot:trigger>

                                            <form id="member-permissions-form-{{ $member->id }}" wire:submit="updateMemberPermissions({{ $member->id }})" class="space-y-4">
                                                @if ($team->owner_user_id === $member->id)
                                                    <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Team owner always has full permissions across this workspace.') }}</p>
                                                @else
                                                    <div class="grid gap-3 md:grid-cols-2">
                                                        @foreach ($teamPermissionLabels as $permissionKey => $permissionLabel)
                                                            <x-ui.checkbox
                                                                wire:model="memberPermissions.{{ $member->id }}"
                                                                :value="$permissionKey"
                                                                :label="$permissionLabel"
                                                            />
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </form>

                                            <x-slot:footer>
                                                <div class="flex items-center justify-end gap-3">
                                                    @if ($team->owner_user_id !== $member->id)
                                                        <x-ui.button
                                                            type="button"
                                                            size="sm"
                                                            variant="outline"
                                                            wire:click="updateMemberPermissions({{ $member->id }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="updateMemberPermissions({{ $member->id }})"
                                                            data-no-loading
                                                            x-on:click="window.__appShellCurrent?.suppressLoadingFor?.(4000)"
                                                        >
                                                            <span wire:loading.remove wire:target="updateMemberPermissions({{ $member->id }})">{{ __('Save permissions') }}</span>
                                                            <span wire:loading wire:target="updateMemberPermissions({{ $member->id }})">{{ __('Saving...') }}</span>
                                                        </x-ui.button>
                                                    @endif
                                                </div>
                                            </x-slot:footer>
                                        </x-ui.modal>

                                        @if ($teamModel && in_array('channels', $enabled_modules, true) && $workspaceAccounts->isNotEmpty())
                                            <x-ui.modal
                                                width="lg"
                                                :title="__('Managed accounts')"
                                                :description="$team->owner_user_id === $member->id ? __('Owner can access all workspace accounts.') : __('Choose which channel accounts this member can access and manage.')"
                                                body-class="overflow-visible max-h-none px-6 py-6"
                                            >
                                                <x-slot:trigger>
                                                    <x-ui.button type="button" size="sm" variant="outline">{{ __('Manage accounts') }}</x-ui.button>
                                                </x-slot:trigger>

                                                <form id="member-accounts-form-{{ $member->id }}" wire:submit="updateMemberManagedAccounts({{ $member->id }})" class="space-y-4">
                                                    @if ($team->owner_user_id === $member->id)
                                                        <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Team owner always has access to all workspace accounts.') }}</p>
                                                    @else
                                                        <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Select the channel accounts this member is allowed to view and manage. If no account is assigned, they will not be able to manage any channels.') }}</p>
                                                        <div class="grid gap-3 md:grid-cols-2">
                                                            @foreach ($workspaceAccounts as $workspaceAccount)
                                                                <x-ui.checkbox
                                                                    wire:model.live="memberManagedAccounts.{{ $member->id }}"
                                                                    data-no-loading
                                                                    :value="$workspaceAccount->id"
                                                                    :label="$workspaceAccount->display_name ?: (($workspaceAccount->username ? '@'.$workspaceAccount->username.' ' : '').str($workspaceAccount->provider_key)->headline())"
                                                                />
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </form>

                                                <x-slot:footer>
                                                    <div class="flex items-center justify-end gap-3">
                                                    @if ($team->owner_user_id !== $member->id)
                                                        <x-ui.button
                                                            type="button"
                                                            size="sm"
                                                            variant="outline"
                                                            wire:click="updateMemberManagedAccounts({{ $member->id }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="updateMemberManagedAccounts({{ $member->id }})"
                                                            data-no-loading
                                                            x-on:click="window.__appShellCurrent?.suppressLoadingFor?.(4000)"
                                                        >
                                                            <span wire:loading.remove wire:target="updateMemberManagedAccounts({{ $member->id }})">{{ __('Save account access') }}</span>
                                                            <span wire:loading wire:target="updateMemberManagedAccounts({{ $member->id }})">{{ __('Saving...') }}</span>
                                                        </x-ui.button>
                                                    @endif
                                                    </div>
                                                </x-slot:footer>
                                            </x-ui.modal>
                                        @endif

                                        @if ($team->owner_user_id !== $member->id)
                                            <x-ui.button type="button" size="sm" variant="danger" wire:click="removeMember({{ $member->id }})">{{ __('Remove') }}</x-ui.button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                @if ($canManageInvites)
                    <div class="rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);" x-show="teamsTab === 'overview'" x-cloak>
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Pending invites') }}</p>
                                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Share code-based invites, track expiry dates, and remove access before they are redeemed.') }}</p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="background-color: rgba(59,130,246,0.10); color: var(--theme-header-text-color);">
                                {{ number_format($pendingInvites->count()) }} {{ __('active') }}
                            </span>
                        </div>
                        <div class="space-y-4 px-5 py-5">
                            @forelse ($pendingInvites as $invite)
                                <div
                                    class="overflow-hidden rounded-[1.15rem] border"
                                    style="border-color: rgba(var(--theme-border-color-rgb), 0.6); background:
                                        radial-gradient(circle at top right, rgba(59,130,246,0.09), transparent 28%),
                                        color-mix(in srgb, var(--theme-surface-base) 86%, transparent);"
                                >
                                    <div
                                        x-data="{
                                            codeCopied: false,
                                            linkCopied: false,
                                            async copyCode() {
                                                await navigator.clipboard.writeText('{{ $invite->invite_code }}');
                                                this.codeCopied = true;
                                                setTimeout(() => this.codeCopied = false, 1500);
                                            },
                                            async copyLink() {
                                                await navigator.clipboard.writeText('{{ $invite->join_url }}');
                                                this.linkCopied = true;
                                                setTimeout(() => this.linkCopied = false, 1500);
                                            }
                                        }"
                                        class="grid gap-5 p-5 xl:grid-cols-[minmax(0,1.35fr)_20rem]"
                                    >
                                        <div class="min-w-0 space-y-4">
                                            <div class="flex flex-wrap items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <p class="truncate text-base font-semibold" style="color: var(--theme-header-text-color);">{{ $invite->email ?: __('Invite by code only') }}</p>
                                                        <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em]" style="background-color: rgba(59,130,246,0.10); color: var(--theme-header-text-color);">
                                                            {{ __(str($invite->role)->headline()->toString()) }}
                                                        </span>
                                                    </div>
                                                    <p class="mt-2 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Use this code or link to join the workspace.') }}</p>
                                                </div>
                                                <x-ui.button
                                                    type="button"
                                                    size="sm"
                                                    variant="outline"
                                                    wire:click="deleteInvite({{ $invite->id }})"
                                                    wire:confirm="{{ __('Delete this invite?') }}"
                                                >
                                                    {{ __('Delete') }}
                                                </x-ui.button>
                                            </div>

                                            <div class="grid gap-3 sm:grid-cols-3">
                                                <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 92%, transparent);">
                                                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Invite code') }}</p>
                                                    <p class="mt-2 text-sm font-semibold tracking-[0.12em]" style="color: var(--theme-header-text-color);">{{ $invite->invite_code }}</p>
                                                </div>
                                                <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 92%, transparent);">
                                                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Expires') }}</p>
                                                    <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $invite->expires_at?->format('M j, Y') ?: '-' }}</p>
                                                </div>
                                                <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 92%, transparent);">
                                                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Invited by') }}</p>
                                                    <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $invite->inviter?->name ?: __('System') }}</p>
                                                </div>
                                            </div>

                                            <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
                                                <div class="flex flex-wrap items-center justify-between gap-2">
                                                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Join link') }}</p>
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <button
                                                            type="button"
                                                            class="inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold transition"
                                                            style="border-color: rgba(var(--theme-border-color-rgb), 0.55); color: var(--theme-header-text-color);"
                                                            x-on:click="copyCode"
                                                        >
                                                            <span x-show="!codeCopied">{{ __('Copy code') }}</span>
                                                            <span x-show="codeCopied" style="color: var(--theme-accent);">{{ __('Copied code') }}</span>
                                                        </button>
                                                        <button
                                                            type="button"
                                                            class="inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold transition"
                                                            style="border-color: rgba(var(--theme-border-color-rgb), 0.55); color: var(--theme-header-text-color);"
                                                            x-on:click="copyLink"
                                                        >
                                                            <span x-show="!linkCopied">{{ __('Copy link') }}</span>
                                                            <span x-show="linkCopied" style="color: var(--theme-accent);">{{ __('Copied link') }}</span>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="mt-3 rounded-[0.9rem] border px-3 py-3 text-xs leading-6 break-all" style="border-color: rgba(var(--theme-border-color-rgb), 0.48); color: var(--theme-header-text-color); background-color: color-mix(in srgb, var(--theme-surface-overlay) 94%, transparent);">
                                                    {{ $invite->join_url }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="space-y-4">
                                            <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('QR join') }}</p>
                                                <div class="mt-3 flex justify-center rounded-[0.9rem] p-4" style="background-color: #ffffff;">
                                                    {!! $invite->join_qr_svg !!}
                                                </div>
                                            </div>

                                            <div class="rounded-[1rem] border px-4 py-4 text-sm leading-7" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 92%, transparent);">
                                                {{ __('Members can join by entering the code, opening the link, or scanning the QR code.') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="py-4">
                                    <x-ui.empty icon="fa-light fa-ticket" :title="__('No Pending Invites')" :description="__('Create invite links or codes for new members here.')"/>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif

                <div class="grid gap-4 xl:grid-cols-2" x-show="teamsTab === 'approvals'" x-cloak>
                    @if ($publishingEnabled)
                    <div class="rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                        <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Approval queue') }}</p>
                        </div>
                        <div class="divide-y" style="divide-color: rgba(var(--theme-border-color-rgb), 0.68);">
                            @forelse ($reviews as $review)
                                <div class="px-5 py-4">
                                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $review->post?->data['title'] ?? __('Untitled post') }}</p>
                                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Status: :status | Submitted by :user', ['status' => __(str($review->status)->headline()->toString()), 'user' => $review->submitter?->name ?: __('Unknown')]) }}</p>
                                    @if ($review->decision_note)
                                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $review->decision_note }}</p>
                                    @endif
                                    @if ($canApprovePosts && $review->status === 'pending')
                                        <div class="mt-3">
                                            <x-ui.textarea wire:model="reviewNotes.{{ $review->id }}" name="review_note_{{ $review->id }}" :label="__('Decision note')" :error="$errors->first('reviewNotes.'.$review->id)" rows="2">{{ $reviewNotes[$review->id] ?? '' }}</x-ui.textarea>
                                        </div>
                                        <div class="mt-3 flex items-center gap-2">
                                            <x-ui.button type="button" size="sm" wire:click="approveReview({{ $review->id }})">{{ __('Approve') }}</x-ui.button>
                                            <x-ui.button type="button" size="sm" variant="danger" wire:click="rejectReview({{ $review->id }})">{{ __('Reject') }}</x-ui.button>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="px-5 py-8">
                                    <x-ui.empty icon="fa-light fa-badge-check" :title="__('No approval items yet')" :description="__('Publishing approvals will appear here once member-created posts are routed for review.')"/>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    @endif

                    <div class="rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                        <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Team discussion') }}</p>
                        </div>
                        <div class="divide-y" style="divide-color: rgba(var(--theme-border-color-rgb), 0.68);">
                            @forelse ($comments as $comment)
                                <div class="px-5 py-4">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $comment->author_name ?: __('Unknown') }}</p>
                                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ $comment->title }}</p>
                                        </div>
                                        @if (!empty($comment->created_at))
                                            <span class="shrink-0 text-xs" style="color: var(--theme-muted-text-color);">{{ $comment->created_at->diffForHumans() }}</span>
                                        @endif
                                    </div>
                                    @if (!empty($comment->meta))
                                        <p class="mt-2 text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $comment->meta }}</p>
                                    @endif
                                    <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $comment->message }}</p>
                                </div>
                            @empty
                                <div class="px-5 py-8">
                                    <x-ui.empty icon="fa-light fa-comments" :title="__('No discussion yet')" :description="__('Post comments and approval conversations will appear here once the team starts collaborating.')"/>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                @if ($chatEnabled)
                <div
                    class="rounded-[1.25rem] border"
                    style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);"
                    x-data="teamConversationStream(@js($conversationStreamPayload), @js(route('portal.teams.stream')), @js(__('Use this room to coordinate publishing work, requests, and quick decisions.')), @js(__('Send the first message to start this room.')), @js(__('Select a conversation on the left or create a new room to start chatting.')))"
                    x-on:team-chat-scroll-bottom.window="$nextTick(() => { scrollMessagesToBottom(); window.dispatchEvent(new CustomEvent('team-chat-stream-resume')); })"
                    x-show="teamsTab === 'chat'"
                    x-cloak
                >
                    <div class="border-b px-5 py-4 sm:px-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Conversations') }}</p>
                                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Create rooms, open a thread, and send messages with your team here.') }}</p>
                            </div>
                            @if ($canManageChat)
                                <x-ui.button type="button" x-on:click="$dispatch('team-conversation-create-modal-open')">
                                    <i class="fa-light fa-plus mr-2"></i>{{ __('New room') }}
                                </x-ui.button>
                            @endif
                        </div>
                    </div>
                    @if ($canUseChat)
                        <div class="grid gap-0 xl:min-h-[54rem] xl:grid-cols-[18rem_minmax(0,1fr)]">
                            <div class="flex min-h-0 flex-col border-b xl:border-b-0 xl:border-r px-5 py-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
                                linear-gradient(180deg, rgba(var(--theme-accent-rgb), 0.05) 0%, transparent 24%),
                                color-mix(in srgb, var(--theme-surface-base) 95%, transparent);">
                                <x-ui.modal
                                    width="lg"
                                    open-event="team-conversation-create-modal-open"
                                    close-event="team-conversation-create-modal-close"
                                    :title="__('Create room')"
                                    :description="__('Start a new team conversation with a room name, topic, and optional first message.')"
                                    body-class="overflow-visible max-h-none px-6 py-6"
                                >
                                    <x-slot:trigger>
                                        <span class="hidden"></span>
                                    </x-slot:trigger>

                                    <form id="team-conversation-create-form" wire:submit="storeConversation" class="space-y-4">
                                        <x-ui.input wire:model="conversation_title" name="conversation_title" :label="__('Room name')" :error="$errors->first('conversation_title')" :placeholder="__('Content planning')" />
                                        <x-ui.select wire:model="conversation_description" name="conversation_description" :label="__('Topic')" :error="$errors->first('conversation_description')">
                                            <option value="">{{ __('Select a topic') }}</option>
                                            @foreach ($conversationTopicOptions as $topicValue => $topicLabel)
                                                <option value="{{ $topicValue }}">{{ $topicLabel }}</option>
                                            @endforeach
                                        </x-ui.select>
                                        <x-ui.textarea wire:model="initial_message" name="initial_message" :label="__('First message')" :error="$errors->first('initial_message')" rows="4">{{ $initial_message }}</x-ui.textarea>
                                    </form>

                                    <x-slot:footer>
                                        <div class="flex items-center justify-end gap-3">
                                            <x-ui.button type="button" variant="outline" x-on:click="$dispatch('team-conversation-create-modal-close')">{{ __('Cancel') }}</x-ui.button>
                                            <x-ui.button type="submit" form="team-conversation-create-form">{{ __('Create room') }}</x-ui.button>
                                        </div>
                                    </x-slot:footer>
                                </x-ui.modal>

                                <div class="mb-4">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Rooms') }}</p>
                                    <p class="mt-1 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ trans_choice(':count active room|:count active rooms', $conversationCount, ['count' => $conversationCount]) }}</p>
                                </div>

                                <div class="min-h-0 max-h-[2000px] flex-1 overflow-y-auto pr-1">
                                    <template x-if="conversations.length">
                                        <div class="space-y-2">
                                            <template x-for="conversationItem in conversations" :key="conversationItem.id">
                                                <a
                                                    :href="conversationItem.href"
                                                    class="group block rounded-[1.05rem] border px-3.5 py-3.5 transition duration-150 ease-out hover:-translate-y-[1px]"
                                                    :style="conversationItem.active
                                                        ? 'border-color: rgba(59,130,246,0.32); background-color: rgba(59,130,246,0.08); box-shadow: 0 18px 40px -30px rgba(59,130,246,0.40);'
                                                        : 'border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.88);'"
                                                >
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div class="min-w-0">
                                                            <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);" x-text="conversationItem.title"></p>
                                                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);" x-text="conversationItem.messages_label"></p>
                                                        </div>
                                                        <span class="text-[11px]" style="color: var(--theme-muted-text-color);" x-text="conversationItem.last_message_label"></span>
                                                    </div>
                                                </a>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="!conversations.length">
                                        <x-ui.empty icon="fa-light fa-message-lines" :title="__('No conversations yet')" :description="__('Create the first room to start chatting with your team.')"/>
                                    </template>
                                </div>
                            </div>

                            <div class="flex min-h-0 flex-col">
                                <template x-if="activeConversation">
                                    <div class="flex h-full min-h-[54rem] flex-col">
                                        <div class="border-b px-5 py-5 sm:px-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.6); background:
                                            radial-gradient(circle at top right, rgba(var(--theme-accent-rgb), 0.08), transparent 32%),
                                            color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
                                            <div class="flex flex-wrap items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Active room') }}</p>
                                                    <p class="mt-2 text-[1.15rem] font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);" x-text="activeConversation?.title || @js(__('Untitled room'))"></p>
                                                    <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);" x-text="activeConversation?.description || emptyDescription"></p>
                                                </div>
                                                <div class="flex flex-wrap items-center justify-end gap-2">
                                                    <div class="rounded-full px-3 py-1 text-[11px] font-medium" style="background-color: rgba(var(--theme-border-color-rgb), 0.08); color: var(--theme-muted-text-color);" x-text="activeConversation?.participants_label || ''"></div>
                                                    @if ($canManageChat)
                                                        <x-ui.modal
                                                            width="lg"
                                                            open-event="team-conversation-edit-modal-open"
                                                            close-event="team-conversation-edit-modal-close"
                                                            :title="__('Edit room')"
                                                            :description="__('Update the room title and topic for everyone in this workspace.')"
                                                            body-class="overflow-visible max-h-none px-6 py-6"
                                                        >
                                                            <x-slot:trigger>
                                                                <x-ui.button type="button" size="sm" variant="outline" wire:click="beginEditConversation">{{ __('Edit room') }}</x-ui.button>
                                                            </x-slot:trigger>

                                                            <form id="team-conversation-edit-form" wire:submit="updateConversation" class="space-y-4">
                                                                <x-ui.input wire:model="edit_conversation_title" name="edit_conversation_title" :label="__('Room name')" :error="$errors->first('edit_conversation_title')" />
                                                                <x-ui.select wire:model="edit_conversation_description" name="edit_conversation_description" :label="__('Topic')" :error="$errors->first('edit_conversation_description')">
                                                                    <option value="">{{ __('Select a topic') }}</option>
                                                                    @if ($edit_conversation_description !== '' && ! array_key_exists($edit_conversation_description, $conversationTopicOptions))
                                                                        <option value="{{ $edit_conversation_description }}">{{ $edit_conversation_description }}</option>
                                                                    @endif
                                                                    @foreach ($conversationTopicOptions as $topicValue => $topicLabel)
                                                                        <option value="{{ $topicValue }}">{{ $topicLabel }}</option>
                                                                    @endforeach
                                                                </x-ui.select>
                                                            </form>

                                                            <x-slot:footer>
                                                                <div class="flex items-center justify-end gap-3">
                                                                    <x-ui.button type="button" variant="outline" x-on:click="$dispatch('team-conversation-edit-modal-close')">{{ __('Cancel') }}</x-ui.button>
                                                                    <x-ui.button type="submit" form="team-conversation-edit-form">{{ __('Save changes') }}</x-ui.button>
                                                                </div>
                                                            </x-slot:footer>
                                                        </x-ui.modal>

                                                        <x-ui.button
                                                            type="button"
                                                            size="sm"
                                                            variant="danger"
                                                            wire:click="deleteConversation"
                                                            wire:confirm="{{ __('Delete this room?') }}"
                                                        >
                                                            {{ __('Delete room') }}
                                                        </x-ui.button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="min-h-0 flex-1 px-3 py-3 sm:px-4 sm:py-4">
                                            <div class="max-h-[2000px] space-y-3 overflow-y-auto pr-1" x-ref="messageList">
                                                    <template x-if="activeConversation?.messages?.length">
                                                        <div class="space-y-3">
                                                            <template x-for="messageItem in activeConversation.messages" :key="messageItem.id">
                                                                <div class="rounded-[1.05rem] border px-4 py-4 shadow-[0_14px_36px_-34px_rgba(15,23,42,0.3)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
                                                                    <div class="flex items-start gap-3">
                                                                        <div class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-xs font-semibold uppercase" style="background-color: rgba(var(--theme-accent-rgb), 0.10); color: var(--theme-header-text-color);" x-text="String(messageItem.author_name || '?').split(' ').filter(Boolean).slice(0, 2).map((part) => part.charAt(0)).join('').slice(0, 2) || '?'"></div>
                                                                        <div class="min-w-0 flex-1">
                                                                            <div class="flex items-center justify-between gap-3">
                                                                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);" x-text="messageItem.author_name"></p>
                                                                                <span class="shrink-0 text-xs" style="color: var(--theme-muted-text-color);" x-text="messageItem.sent_label"></span>
                                                                            </div>
                                                                            <p class="mt-2 whitespace-pre-line text-sm leading-7" style="color: var(--theme-muted-text-color);" x-text="messageItem.body"></p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </template>
                                                    <template x-if="activeConversation && !(activeConversation.messages || []).length">
                                                        <x-ui.empty icon="fa-light fa-comments" :title="__('No messages yet')" :description="__('Send the first message to start this room.')"/>
                                                    </template>
                                            </div>
                                        </div>

                                        <div
                                            class="border-t px-4 py-5 sm:px-5"
                                            style="border-color: rgba(var(--theme-border-color-rgb), 0.6); background:
                                            linear-gradient(180deg, rgba(var(--theme-accent-rgb), 0.03) 0%, transparent 36%),
                                            color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                                            <div class="rounded-[1.2rem] border p-4 shadow-[0_18px_42px_-38px_rgba(15,23,42,0.24)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78);">
                                                <div class="space-y-2.5">
                                                    <x-ui.label>{{ __('Message') }}</x-ui.label>
                                                    <textarea
                                                        wire:model.defer="body"
                                                        name="body"
                                                        rows="5"
                                                        class="flex min-h-32 w-full border px-4 py-3.5 text-sm leading-6 shadow-[0_1px_2px_rgba(15,23,42,0.04)] outline-none transition duration-200 placeholder:text-[var(--theme-input-placeholder)] focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]"
                                                        style="border-radius: var(--theme-input-radius, 0.75rem); border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);"
                                                    >{{ $body }}</textarea>
                                                    @if ($errors->first('body'))
                                                        <p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $errors->first('body') }}</p>
                                                    @endif
                                                </div>
                                                <div class="mt-4 flex justify-end">
                                                    <x-ui.button type="button" wire:click="storeMessage" wire:loading.attr="disabled" wire:target="storeMessage" x-on:click="window.dispatchEvent(new CustomEvent('team-chat-stream-pause')); window.__appShellCurrent?.suppressLoadingFor?.(4000)">
                                                        <span wire:loading.remove wire:target="storeMessage">{{ __('Send message') }}</span>
                                                        <span wire:loading wire:target="storeMessage">{{ __('Sending...') }}</span>
                                                    </x-ui.button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!activeConversation">
                                    <div class="flex h-full min-h-[30rem] items-center justify-center px-6 py-10">
                                        <x-ui.empty icon="fa-light fa-comments" :title="__('Open a room')" :description="__('Select a conversation on the left or create a new room to start chatting.')"/>
                                    </div>
                                </template>
                            </div>
                        </div>
                    @else
                        <div class="px-5 py-8">
                            <x-ui.empty icon="fa-light fa-message-lines" :title="__('Chat unavailable')" :description="__('You do not currently have access to team chat.')"/>
                        </div>
                    @endif
                </div>
                @endif
                </div>
            </section>
        </div>
        @else
        @php($currentMember = $team->members->firstWhere('id', $user->id))
        @php($currentPermissions = $currentMember ? ($memberPermissions[$currentMember->id] ?? []) : [])
        <div class="grid gap-4 sm:grid-cols-2 {{ $chatEnabled ? 'xl:grid-cols-3' : 'xl:grid-cols-2' }}">
            <div class="rounded-[1.15rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 96%, transparent);">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Members') }}</p>
                <p class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($memberCount) }}</p>
            </div>
            <div class="rounded-[1.15rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 96%, transparent);">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('My role') }}</p>
                <p class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ __(str(($currentMember->pivot->role ?? 'member'))->headline()->toString()) }}</p>
            </div>
            @if ($chatEnabled)
                <div class="rounded-[1.15rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(16,185,129,0.05);">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Conversations') }}</p>
                    <p class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($conversationCount) }}</p>
                </div>
            @endif
        </div>

        <div
            x-data="{
                memberTab: @js(in_array($teams_tab ?? 'overview', ['overview', 'chat'], true) ? $teams_tab : 'overview'),
                init() {
                    const urlTab = new URL(window.location.href).searchParams.get('teams_tab');
                    if (['overview', 'chat'].includes(urlTab)) {
                        this.memberTab = urlTab;
                    }
                },
                setMemberTab(tab) {
                    if (! ['overview', 'chat'].includes(tab)) {
                        tab = 'overview';
                    }

                    this.memberTab = tab;

                    const url = new URL(window.location.href);

                    if (tab === 'overview') {
                        url.searchParams.delete('teams_tab');
                    } else {
                        url.searchParams.set('teams_tab', tab);
                    }

                    window.history.replaceState({}, '', url);
                }
            }"
            class="space-y-4"
        >
            <div class="flex flex-wrap gap-2">
                <button type="button" x-on:click="setMemberTab('overview')" class="rounded-full border px-4 py-2 text-sm font-semibold transition" :style="memberTab === 'overview' ? 'border-color: rgba(59,130,246,0.28); background-color: rgba(59,130,246,0.10); color: var(--theme-header-text-color);' : 'border-color: rgba(var(--theme-border-color-rgb), 0.6); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent); color: var(--theme-muted-text-color);'">{{ __('Overview') }}</button>
                @if ($chatEnabled)
                    <button type="button" x-on:click="setMemberTab('chat')" class="rounded-full border px-4 py-2 text-sm font-semibold transition" :style="memberTab === 'chat' ? 'border-color: rgba(16,185,129,0.28); background-color: rgba(16,185,129,0.10); color: var(--theme-header-text-color);' : 'border-color: rgba(var(--theme-border-color-rgb), 0.6); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent); color: var(--theme-muted-text-color);'">{{ __('Chat') }}</button>
                @endif
            </div>

        <div
            class="grid gap-4 xl:grid-cols-[22rem_minmax(0,1fr)]"
            :style="memberTab === 'overview' ? '' : 'grid-template-columns: minmax(0,1fr);'"
        >
            <section class="space-y-4" x-show="memberTab === 'overview'" x-cloak>
                <div class="rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                    <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Team profile') }}</p>
                    </div>
                    <div class="space-y-4 px-5 py-5">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Team name') }}</p>
                            <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $team->name }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Description') }}</p>
                            <p class="mt-2 text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ $team->description ?: __('No team description yet.') }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Enabled modules') }}</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($enabled_modules as $moduleKey)
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em]" style="background-color: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-header-text-color);">
                                        {{ $teamModuleLabels[$moduleKey] ?? str($moduleKey)->headline() }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                    <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('My access') }}</p>
                    </div>
                    <div class="space-y-4 px-5 py-5">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Role') }}</p>
                            <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __(str(($currentMember->pivot->role ?? 'member'))->headline()->toString()) }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Permissions') }}</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @forelse ($currentPermissions as $permission)
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em]" style="background-color: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-header-text-color);">
                                        {{ $teamPermissionLabels[$permission] ?? str($permission)->headline() }}
                                    </span>
                                @empty
                                    <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('No extra permissions assigned.') }}</p>
                                @endforelse
                            </div>
                        </div>
                        @if ($workspaceAccounts->isNotEmpty())
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Assigned accounts') }}</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @php($currentManagedAccounts = $workspaceAccounts->whereIn('id', $memberManagedAccounts[$currentMember->id] ?? []))
                                    @forelse ($currentManagedAccounts as $workspaceAccount)
                                        <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em]" style="background-color: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-header-text-color);">
                                            {{ $workspaceAccount->display_name ?: (($workspaceAccount->username ? '@'.$workspaceAccount->username.' ' : '').str($workspaceAccount->provider_key)->headline()) }}
                                        </span>
                                    @empty
                                        <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('No channel accounts assigned yet.') }}</p>
                                    @endforelse
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            <section class="space-y-4">
                <div class="rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);" x-show="memberTab === 'overview'" x-cloak>
                    <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Members') }}</p>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('People currently active in this workspace.') }}</p>
                    </div>
                    <div class="divide-y" style="divide-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        @foreach ($team->members as $member)
                            <div class="px-5 py-4">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $member->name }}</p>
                                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ '@'.$member->username }} | {{ $member->email }}</p>
                                    </div>
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em]" style="background-color: rgba(var(--theme-border-color-rgb), 0.08); color: var(--theme-header-text-color);">
                                        {{ __(str(($member->pivot->role ?? 'member'))->headline()->toString()) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if ($chatEnabled)
                <div
                    class="rounded-[1.25rem] border"
                    style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);"
                    x-data="teamConversationStream(@js($conversationStreamPayload), @js(route('portal.teams.stream')), @js(__('Use this room to coordinate work and quick decisions.')), @js(__('Send the first message to start this room.')), @js(__('Select a conversation on the left to start chatting.')))"
                    x-on:team-chat-scroll-bottom.window="$nextTick(() => { scrollMessagesToBottom(); window.dispatchEvent(new CustomEvent('team-chat-stream-resume')); })"
                    x-show="memberTab === 'chat'"
                    x-cloak
                >
                    <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Conversations') }}</p>
                                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Open a room and chat with your team here.') }}</p>
                            </div>
                            @if ($canManageChat)
                                <x-ui.button type="button" x-on:click="$dispatch('team-conversation-create-modal-open')">
                                    <i class="fa-light fa-plus mr-2"></i>{{ __('New room') }}
                                </x-ui.button>
                            @endif
                        </div>
                    </div>
                    @if ($canUseChat)
                        <div class="grid gap-0 xl:min-h-[54rem] xl:grid-cols-[18rem_minmax(0,1fr)]">
                            <div class="flex min-h-0 flex-col border-b xl:border-b-0 xl:border-r px-5 py-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
                                linear-gradient(180deg, rgba(var(--theme-accent-rgb), 0.05) 0%, transparent 24%),
                                color-mix(in srgb, var(--theme-surface-base) 95%, transparent);">
                                @if ($canManageChat)
                                    <x-ui.modal
                                        width="lg"
                                        open-event="team-conversation-create-modal-open"
                                        close-event="team-conversation-create-modal-close"
                                        :title="__('Create room')"
                                        :description="__('Start a new team conversation with a room name, topic, and optional first message.')"
                                        body-class="overflow-visible max-h-none px-6 py-6"
                                    >
                                        <x-slot:trigger>
                                            <span class="hidden"></span>
                                        </x-slot:trigger>

                                        <form id="team-member-conversation-create-form" wire:submit="storeConversation" class="space-y-4">
                                            <x-ui.input wire:model="conversation_title" name="conversation_title" :label="__('Room name')" :error="$errors->first('conversation_title')" :placeholder="__('Content planning')" />
                                            <x-ui.select wire:model="conversation_description" name="conversation_description" :label="__('Topic')" :error="$errors->first('conversation_description')">
                                                <option value="">{{ __('Select a topic') }}</option>
                                                @foreach ($conversationTopicOptions as $topicValue => $topicLabel)
                                                    <option value="{{ $topicValue }}">{{ $topicLabel }}</option>
                                                @endforeach
                                            </x-ui.select>
                                            <x-ui.textarea wire:model="initial_message" name="initial_message" :label="__('First message')" :error="$errors->first('initial_message')" rows="4">{{ $initial_message }}</x-ui.textarea>
                                        </form>

                                        <x-slot:footer>
                                            <div class="flex items-center justify-end gap-3">
                                                <x-ui.button type="button" variant="outline" x-on:click="$dispatch('team-conversation-create-modal-close')">{{ __('Cancel') }}</x-ui.button>
                                                <x-ui.button type="submit" form="team-member-conversation-create-form">{{ __('Create room') }}</x-ui.button>
                                            </div>
                                        </x-slot:footer>
                                    </x-ui.modal>
                                @endif

                                <div class="mb-4">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Rooms') }}</p>
                                    <p class="mt-1 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ trans_choice(':count active room|:count active rooms', $conversationCount, ['count' => $conversationCount]) }}</p>
                                </div>

                                <div class="min-h-0 max-h-[2000px] flex-1 overflow-y-auto pr-1">
                                    <template x-if="conversations.length">
                                        <div class="space-y-2">
                                            <template x-for="conversationItem in conversations" :key="conversationItem.id">
                                                <a
                                                    :href="conversationItem.href"
                                                    class="group block rounded-[1.05rem] border px-3.5 py-3.5 transition duration-150 ease-out hover:-translate-y-[1px]"
                                                    :style="conversationItem.active
                                                        ? 'border-color: rgba(59,130,246,0.32); background-color: rgba(59,130,246,0.08); box-shadow: 0 18px 40px -30px rgba(59,130,246,0.40);'
                                                        : 'border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.88);'"
                                                >
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div class="min-w-0">
                                                            <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);" x-text="conversationItem.title"></p>
                                                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);" x-text="conversationItem.messages_label"></p>
                                                        </div>
                                                        <span class="text-[11px]" style="color: var(--theme-muted-text-color);" x-text="conversationItem.last_message_label"></span>
                                                    </div>
                                                </a>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="!conversations.length">
                                        <x-ui.empty icon="fa-light fa-message-lines" :title="__('No conversations yet')" :description="__('A manager can create the first room for this workspace.')"/>
                                    </template>
                                </div>
                            </div>

                            <div class="flex min-h-0 flex-col">
                                <template x-if="activeConversation">
                                    <div class="flex h-full min-h-[54rem] flex-col">
                                        <div class="border-b px-5 py-5 sm:px-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.6); background:
                                            radial-gradient(circle at top right, rgba(var(--theme-accent-rgb), 0.08), transparent 32%),
                                            color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
                                            <div class="flex flex-wrap items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Active room') }}</p>
                                                    <p class="mt-2 text-[1.15rem] font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);" x-text="activeConversation?.title || @js(__('Untitled room'))"></p>
                                                    <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);" x-text="activeConversation?.description || emptyDescription"></p>
                                                </div>
                                                <div class="flex flex-wrap items-center justify-end gap-2">
                                                    <div class="rounded-full px-3 py-1 text-[11px] font-medium" style="background-color: rgba(var(--theme-border-color-rgb), 0.08); color: var(--theme-muted-text-color);" x-text="activeConversation?.participants_label || ''"></div>
                                                    @if ($canManageChat)
                                                        <x-ui.modal
                                                            width="lg"
                                                            open-event="team-conversation-edit-modal-open"
                                                            close-event="team-conversation-edit-modal-close"
                                                            :title="__('Edit room')"
                                                            :description="__('Update the room title and topic for everyone in this workspace.')"
                                                            body-class="overflow-visible max-h-none px-6 py-6"
                                                        >
                                                            <x-slot:trigger>
                                                                <x-ui.button type="button" size="sm" variant="outline" wire:click="beginEditConversation">{{ __('Edit room') }}</x-ui.button>
                                                            </x-slot:trigger>

                                                            <form id="team-member-conversation-edit-form" wire:submit="updateConversation" class="space-y-4">
                                                                <x-ui.input wire:model="edit_conversation_title" name="edit_conversation_title" :label="__('Room name')" :error="$errors->first('edit_conversation_title')" />
                                                                <x-ui.select wire:model="edit_conversation_description" name="edit_conversation_description" :label="__('Topic')" :error="$errors->first('edit_conversation_description')">
                                                                    <option value="">{{ __('Select a topic') }}</option>
                                                                    @if ($edit_conversation_description !== '' && ! array_key_exists($edit_conversation_description, $conversationTopicOptions))
                                                                        <option value="{{ $edit_conversation_description }}">{{ $edit_conversation_description }}</option>
                                                                    @endif
                                                                    @foreach ($conversationTopicOptions as $topicValue => $topicLabel)
                                                                        <option value="{{ $topicValue }}">{{ $topicLabel }}</option>
                                                                    @endforeach
                                                                </x-ui.select>
                                                            </form>

                                                            <x-slot:footer>
                                                                <div class="flex items-center justify-end gap-3">
                                                                    <x-ui.button type="button" variant="outline" x-on:click="$dispatch('team-conversation-edit-modal-close')">{{ __('Cancel') }}</x-ui.button>
                                                                    <x-ui.button type="submit" form="team-member-conversation-edit-form">{{ __('Save changes') }}</x-ui.button>
                                                                </div>
                                                            </x-slot:footer>
                                                        </x-ui.modal>

                                                        <x-ui.button
                                                            type="button"
                                                            size="sm"
                                                            variant="danger"
                                                            wire:click="deleteConversation"
                                                            wire:confirm="{{ __('Delete this room?') }}"
                                                        >
                                                            {{ __('Delete room') }}
                                                        </x-ui.button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="min-h-0 flex-1 px-3 py-3 sm:px-4 sm:py-4">
                                            <div class="max-h-[2000px] space-y-3 overflow-y-auto pr-1" x-ref="messageList">
                                            <template x-if="activeConversation?.messages?.length">
                                                <div class="space-y-3">
                                                    <template x-for="messageItem in activeConversation.messages" :key="messageItem.id">
                                                        <div class="rounded-[1.05rem] border px-4 py-4 shadow-[0_14px_36px_-34px_rgba(15,23,42,0.3)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
                                                            <div class="flex items-start gap-3">
                                                                <div class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-xs font-semibold uppercase" style="background-color: rgba(var(--theme-accent-rgb), 0.10); color: var(--theme-header-text-color);" x-text="String(messageItem.author_name || '?').split(' ').filter(Boolean).slice(0, 2).map((part) => part.charAt(0)).join('').slice(0, 2) || '?'"></div>
                                                                <div class="min-w-0 flex-1">
                                                                    <div class="flex items-center justify-between gap-3">
                                                                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);" x-text="messageItem.author_name"></p>
                                                                        <span class="shrink-0 text-xs" style="color: var(--theme-muted-text-color);" x-text="messageItem.sent_label"></span>
                                                                    </div>
                                                                    <p class="mt-2 whitespace-pre-line text-sm leading-7" style="color: var(--theme-muted-text-color);" x-text="messageItem.body"></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="activeConversation && !(activeConversation.messages || []).length">
                                                <x-ui.empty icon="fa-light fa-comments" :title="__('No messages yet')" :description="__('Send the first message to start this room.')"/>
                                            </template>
                                            </div>
                                        </div>

                                        <div
                                            class="border-t px-4 py-5 sm:px-5"
                                            style="border-color: rgba(var(--theme-border-color-rgb), 0.6); background:
                                            linear-gradient(180deg, rgba(var(--theme-accent-rgb), 0.03) 0%, transparent 36%),
                                            color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                                            <div class="rounded-[1.2rem] border p-4 shadow-[0_18px_42px_-38px_rgba(15,23,42,0.24)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78);">
                                                <div class="space-y-2.5">
                                                    <x-ui.label>{{ __('Message') }}</x-ui.label>
                                                    <textarea
                                                        wire:model.defer="body"
                                                        name="body"
                                                        rows="5"
                                                        class="flex min-h-32 w-full border px-4 py-3.5 text-sm leading-6 shadow-[0_1px_2px_rgba(15,23,42,0.04)] outline-none transition duration-200 placeholder:text-[var(--theme-input-placeholder)] focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]"
                                                        style="border-radius: var(--theme-input-radius, 0.75rem); border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);"
                                                    >{{ $body }}</textarea>
                                                    @if ($errors->first('body'))
                                                        <p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $errors->first('body') }}</p>
                                                    @endif
                                                </div>
                                                <div class="mt-4 flex justify-end">
                                                    <x-ui.button type="button" wire:click="storeMessage" wire:loading.attr="disabled" wire:target="storeMessage" x-on:click="window.dispatchEvent(new CustomEvent('team-chat-stream-pause')); window.__appShellCurrent?.suppressLoadingFor?.(4000)">
                                                        <span wire:loading.remove wire:target="storeMessage">{{ __('Send message') }}</span>
                                                        <span wire:loading wire:target="storeMessage">{{ __('Sending...') }}</span>
                                                    </x-ui.button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!activeConversation">
                                    <div class="flex h-full min-h-[30rem] items-center justify-center px-6 py-10">
                                        <x-ui.empty icon="fa-light fa-comments" :title="__('Open a room')" :description="__('Select a conversation on the left or create a new room to start chatting.')"/>
                                    </div>
                                </template>
                            </div>
                        </div>
                    @else
                        <div class="px-5 py-8">
                            <x-ui.empty icon="fa-light fa-message-lines" :title="__('Chat unavailable')" :description="__('You do not currently have access to team chat.')"/>
                        </div>
                    @endif
                </div>
                @endif
            </section>
        </div>
        @endif
    @endif
</div>

@once
    <script>
        window.teamConversationStream = function (initialPayload, endpoint, emptyDescription, emptyMessagesDescription, emptyConversationDescription) {
            return {
                endpoint,
                emptyDescription,
                emptyMessagesDescription,
                emptyConversationDescription,
                conversations: Array.isArray(initialPayload?.conversations) ? initialPayload.conversations : [],
                activeConversation: initialPayload?.activeConversation || null,
                activeConversationId: Number(initialPayload?.activeConversationId || initialPayload?.activeConversation?.id || 0),
                teamId: Number(initialPayload?.teamId || 0),
                stream: null,
                init() {
                    this.$nextTick(() => this.scrollMessagesToBottom());

                    window.addEventListener('team-chat-stream-pause', () => this.pauseStream());
                    window.addEventListener('team-chat-stream-resume', () => this.resumeStream());

                    if (!this.teamId || !this.activeConversationId || typeof window.EventSource === 'undefined') {
                        return;
                    }

                    this.connect();

                    window.addEventListener('beforeunload', () => this.disconnect(), { once: true });
                },
                connect() {
                    this.disconnect();

                    const url = new URL(this.endpoint, window.location.origin);
                    url.searchParams.set('team', String(this.teamId));
                    url.searchParams.set('conversation', String(this.activeConversationId));

                    this.stream = new EventSource(url.toString(), { withCredentials: true });
                    this.stream.addEventListener('snapshot', (event) => {
                        try {
                            const payload = JSON.parse(event.data);
                            this.applyPayload(payload);
                        } catch (error) {
                            console.error(error);
                        }
                    });
                },
                disconnect() {
                    if (this.stream) {
                        this.stream.close();
                        this.stream = null;
                    }
                },
                pauseStream() {
                    this.disconnect();
                },
                resumeStream() {
                    if (!this.teamId || !this.activeConversationId || typeof window.EventSource === 'undefined' || this.stream) {
                        return;
                    }

                    this.connect();
                },
                applyPayload(payload) {
                    this.conversations = Array.isArray(payload?.conversations) ? payload.conversations : [];
                    this.activeConversation = payload?.activeConversation || null;
                    this.activeConversationId = Number(payload?.activeConversation?.id || this.activeConversationId || 0);

                    window.dispatchEvent(new CustomEvent('team-chat-snapshot', {
                        detail: {
                            teamId: this.teamId,
                            activeConversationId: this.activeConversationId,
                            conversations: this.conversations,
                        },
                    }));

                    this.$nextTick(() => this.scrollMessagesToBottom());
                },
                scrollMessagesToBottom() {
                    const container = this.$refs.messageList;

                    if (!container) {
                        return;
                    }

                    const scroll = () => {
                        container.scrollTop = container.scrollHeight;
                    };

                    requestAnimationFrame(scroll);
                    setTimeout(scroll, 60);
                },
            };
        };
    </script>
@endonce
