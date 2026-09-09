<?php

namespace Modules\AppTeams\Livewire;

use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\AppChannels\Models\SocialAccount;
use Modules\AppTeams\Models\TeamConversation;
use Modules\AppTeams\Models\TeamInvitation;
use Modules\AppTeams\Models\TeamMessage;
use Modules\AppTeams\Models\TeamPostComment;
use Modules\AppTeams\Models\TeamPostReview;
use Modules\AppTeams\Support\TeamPermissionRegistry;
use Modules\AppTeams\Support\TeamWorkspaceAccess;
use Modules\AppPublishing\Models\PublishingPost;
use Modules\AppPublishing\Jobs\PublishScheduledPostJob;

#[Title('Teams')]
class Workspace extends Component
{
    public string $team = '';

    public string $conversation = '';

    public string $teams_tab = 'overview';

    public string $invite_code = '';

    public string $team_name = '';

    public string $team_description = '';

    public string $email = '';

    public string $role = 'member';

    public string $message = '';

    public string $expires_at = '';

    /** @var array<int, string> */
    public array $enabled_modules = [];

    /** @var array<int, string> */
    public array $invite_permissions = [];

    /** @var array<int, string> */
    public array $inviteManagedAccounts = [];

    /** @var array<int, array<int, string>> */
    public array $memberPermissions = [];

    /** @var array<int, array<int, string>> */
    public array $memberManagedAccounts = [];

    public string $conversation_title = '';

    public string $conversation_description = '';

    public string $edit_conversation_title = '';

    public string $edit_conversation_description = '';

    public string $initial_message = '';

    public string $body = '';

    /** @var array<int, string> */
    public array $reviewNotes = [];

    public ?string $statusMessage = null;

    public ?string $errorMessage = null;

    protected function queryString(): array
    {
        return [
            'team' => ['except' => ''],
            'conversation' => ['except' => ''],
            'teams_tab' => ['except' => 'overview'],
        ];
    }

    public function mount(?string $inviteCode = null): void
    {
        $this->invite_code = strtoupper((string) request()->query('invite', ''));

        if ($inviteCode) {
            $this->acceptInvite($this->user(), $inviteCode);

            return;
        }

        abort_unless($this->teamsEnabled(), 404);

        $this->initializeWorkspace();
    }

    public function updatedTeam(): void
    {
        $this->syncTeamState();
    }

    public function updatedTeamsTab(string $value): void
    {
        if (! in_array($value, ['overview', 'members', 'chat', 'approvals'], true)) {
            $this->teams_tab = 'overview';
        }
    }

    public function switchTeam(): mixed
    {
        $this->syncTeamState();

        return $this->redirectRoute('portal.teams', [
            'team' => $this->team,
        ], navigate: true);
    }

    public function openConversation(int $conversationId): void
    {
        $this->conversation = (string) $conversationId;
    }

    public function refreshConversation(): void
    {
        // Livewire polling re-renders the active chat thread so new messages appear automatically.
    }

    public function redeemInvite(): mixed
    {
        abort_unless($this->teamsEnabled(), 404);

        $validated = $this->validate([
            'invite_code' => ['required', 'string', 'size:8'],
        ]);

        return $this->acceptInvite($this->user(), $validated['invite_code']);
    }

    public function saveTeam(): void
    {
        $team = $this->currentTeamOrFail();
        abort_unless($this->canManageTeam($team, $this->user()->id), 403);

        $availableModules = array_keys($this->availableTeamModules());

        $validated = $this->validate([
            'team_name' => ['required', 'string', 'max:255'],
            'team_description' => ['nullable', 'string', 'max:2000'],
            'enabled_modules' => ['nullable', 'array'],
        ]);

        $team->update([
            'name' => trim($validated['team_name']),
            'description' => trim((string) ($validated['team_description'] ?? '')) ?: null,
            'enabled_modules' => collect((array) ($validated['enabled_modules'] ?? []))
                ->filter(fn ($module) => is_scalar($module) && in_array((string) $module, $availableModules, true))
                ->map(fn ($module) => (string) $module)
                ->unique()
                ->values()
                ->all(),
        ]);

        $this->enabled_modules = TeamWorkspaceAccess::enabledModules($team->fresh());
        $this->notifySuccess(__('Team updated successfully.'));
    }

    public function createInvite(): void
    {
        $team = $this->currentTeamOrFail();
        abort_unless($this->canManageInvites($team, $this->user()->id), 403);

        if ($this->hasReachedTeamMemberLimit($team)) {
            $this->notifyError(__('Your current plan has reached the team member limit.'));

            return;
        }

        $validated = $this->validate([
            'email' => ['nullable', 'email', 'max:255'],
            'message' => ['nullable', 'string', 'max:1000'],
            'expires_at' => ['nullable', 'date'],
            'invite_permissions' => ['nullable', 'array'],
            'invite_permissions.*' => ['string', Rule::in(array_keys($this->availableTeamPermissions($team)))],
            'inviteManagedAccounts' => ['nullable', 'array'],
            'inviteManagedAccounts.*' => ['string', Rule::in(array_map('strval', $this->availableManagedAccountIds($team)))],
        ]);

        $inviteRole = 'member';
        $invitePermissions = collect((array) ($validated['invite_permissions'] ?? []))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($invitePermissions === []) {
            $invitePermissions = $this->defaultPermissionsForRole($inviteRole, $team);
        }

        $inviteManagedAccounts = collect((array) ($validated['inviteManagedAccounts'] ?? []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        TeamInvitation::query()->create([
            'team_id' => $team->id,
            'invited_by_user_id' => $this->user()->id,
            'email' => $validated['email'] ?: null,
            'invite_code' => strtoupper(Str::random(8)),
            'role' => $inviteRole,
            'permissions' => $invitePermissions,
            'status' => 'pending',
            'message' => trim((string) ($validated['message'] ?? '')),
            'expires_at' => ! empty($validated['expires_at']) ? $validated['expires_at'] : now()->addDays(7),
            'metadata' => [
                'delivery' => filled($validated['email'] ?? null) ? 'email' : 'code',
                'managed_account_ids' => $inviteManagedAccounts,
            ],
        ]);

        $this->reset('email', 'message', 'expires_at', 'inviteManagedAccounts');
        $this->invite_permissions = $this->defaultPermissionsForRole($inviteRole, $team);
        $this->role = 'member';
        $this->notifySuccess(__('Invite created successfully.'));
        $this->dispatch('team-invite-modal-close');
    }

    public function deleteInvite(int $inviteId): void
    {
        $team = $this->currentTeamOrFail();
        abort_unless($this->canManageInvites($team, $this->user()->id), 403);

        $invite = TeamInvitation::query()
            ->whereKey($inviteId)
            ->where('team_id', $team->id)
            ->where('status', 'pending')
            ->first();

        if (! $invite) {
            $this->notifyError(__('This invite could not be found or has already been removed.'));

            return;
        }

        $invite->delete();

        $this->notifySuccess(__('Invite deleted successfully.'));
    }

    public function updateMemberPermissions(int $userId): void
    {
        $team = $this->currentTeamOrFail();
        abort_unless($this->canManageMembers($team, $this->user()->id), 403);

        if ((int) $team->owner_user_id === $userId) {
            $this->memberPermissions[$userId] = $this->defaultPermissionsForRole('owner', $team);
            $this->notifySuccess(__('Team owner always has full permissions.'));

            return;
        }

        $validated = $this->validate([
            "memberPermissions.$userId" => ['nullable', 'array'],
            "memberPermissions.$userId.*" => ['string', Rule::in(array_keys($this->availableTeamPermissions($team)))],
        ]);

        $permissions = collect((array) ($validated['memberPermissions'][$userId] ?? []))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $team->members()->updateExistingPivot($userId, [
            'permissions' => $this->encodePermissions($permissions),
        ]);

        $this->memberPermissions[$userId] = $permissions;
        $this->notifySuccess(__('Member permissions updated successfully.'));
    }

    public function updateMemberManagedAccounts(int $userId): void
    {
        $team = $this->currentTeamOrFail();
        abort_unless($this->canManageMembers($team, $this->user()->id), 403);
        abort_unless(TeamWorkspaceAccess::teamHasModule($team, 'channels'), 403);

        if ((int) $team->owner_user_id === $userId) {
            $allAccountIds = array_map('strval', $this->availableManagedAccountIds($team));
            $this->memberManagedAccounts[$userId] = $allAccountIds;
            $this->notifySuccess(__('Team owner always has access to all workspace accounts.'));

            return;
        }

        $availableAccountIds = $this->availableManagedAccountIds($team);
        $availableAccountValues = array_map('strval', $availableAccountIds);

        $validated = $this->validate([
            "memberManagedAccounts.$userId" => ['nullable', 'array'],
            "memberManagedAccounts.$userId.*" => ['string', Rule::in($availableAccountValues)],
        ]);

        $managedAccountIds = collect((array) ($validated['memberManagedAccounts'][$userId] ?? []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $team->members()->updateExistingPivot($userId, [
            'managed_account_ids' => $this->encodeManagedAccountIds($managedAccountIds),
        ]);

        $this->memberManagedAccounts[$userId] = array_map('strval', $managedAccountIds);
        $this->notifySuccess(__('Member account access updated successfully.'));
    }

    public function removeMember(int $userId): void
    {
        $team = $this->currentTeamOrFail();
        abort_unless($this->canManageMembers($team, $this->user()->id), 403);

        if ($team->owner_user_id === $userId) {
            $this->notifyError(__('The team owner cannot be removed. Transfer ownership first.'));

            return;
        }

        $team->members()->detach($userId);
        unset($this->memberPermissions[$userId]);
        $this->notifySuccess(__('Member removed successfully.'));
    }

    public function storeConversation(): mixed
    {
        $team = $this->currentTeamOrFail();
        abort_unless($this->canUseChat($team, $this->user()->id), 403);

        $validated = $this->validate([
            'conversation_title' => ['required', 'string', 'max:255'],
            'conversation_description' => ['nullable', 'string', 'max:1000'],
            'initial_message' => ['nullable', 'string', 'max:5000'],
        ]);

        $conversation = TeamConversation::query()->create([
            'team_id' => $team->id,
            'created_by_user_id' => $this->user()->id,
            'type' => 'room',
            'title' => trim($validated['conversation_title']),
            'description' => trim((string) ($validated['conversation_description'] ?? '')) ?: null,
            'last_message_at' => null,
        ]);

        $participantIds = $team->members()->pluck('users.id')->map(fn ($id) => (int) $id)->all();
        if ((int) $team->owner_user_id > 0) {
            $participantIds[] = (int) $team->owner_user_id;
        }

        $conversation->participants()->syncWithoutDetaching(
            collect($participantIds)
                ->unique()
                ->mapWithKeys(fn ($id) => [$id => ['role' => $id === (int) $team->owner_user_id ? 'owner' : 'member']])
                ->all()
        );

        $initialMessage = trim((string) ($validated['initial_message'] ?? ''));
        if ($initialMessage !== '') {
            TeamMessage::query()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $this->user()->id,
                'body' => $initialMessage,
                'sent_at' => now(),
            ]);

            $conversation->update(['last_message_at' => now()]);
        }

        $this->reset('conversation_title', 'conversation_description', 'initial_message');
        $this->conversation = (string) $conversation->id;
        $this->notifySuccess(__('Conversation created successfully.'));
        $this->dispatch('team-conversation-create-modal-close');

        return $this->redirectRoute('portal.teams', [
            'team' => $team->id,
            'conversation' => $conversation->id,
            'teams_tab' => 'chat',
        ], navigate: true);
    }

    public function storeMessage(?string $body = null): void
    {
        $team = $this->currentTeamOrFail();
        $conversation = $this->activeConversationModel($team);

        abort_if(! $conversation, 404);
        abort_unless($this->canUseChat($team, $this->user()->id), 403);
        abort_unless($this->canAccessConversation($conversation, $this->user()->id), 403);

        if ($body !== null) {
            $this->body = $body;
        }

        $validated = $this->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        TeamMessage::query()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $this->user()->id,
            'body' => trim($validated['body']),
            'sent_at' => now(),
        ]);

        $conversation->update(['last_message_at' => now()]);

        $this->reset('body');
        $this->notifySuccess(__('Message sent successfully.'));
        $this->dispatch('team-chat-scroll-bottom');
    }

    public function beginEditConversation(): void
    {
        $team = $this->currentTeamOrFail();
        $conversation = $this->activeConversationModel($team);

        abort_if(! $conversation, 404);
        abort_unless($this->canManageChat($team, $this->user()->id), 403);

        $this->edit_conversation_title = (string) ($conversation->title ?? '');
        $this->edit_conversation_description = (string) ($conversation->description ?? '');

        $this->dispatch('team-conversation-edit-modal-open');
    }

    public function updateConversation(): void
    {
        $team = $this->currentTeamOrFail();
        $conversation = $this->activeConversationModel($team);

        abort_if(! $conversation, 404);
        abort_unless($this->canManageChat($team, $this->user()->id), 403);

        $validated = $this->validate([
            'edit_conversation_title' => ['required', 'string', 'max:255'],
            'edit_conversation_description' => ['nullable', 'string', 'max:1000'],
        ]);

        $conversation->update([
            'title' => trim($validated['edit_conversation_title']),
            'description' => trim((string) ($validated['edit_conversation_description'] ?? '')) ?: null,
        ]);

        $this->notifySuccess(__('Conversation updated successfully.'));
        $this->dispatch('team-conversation-edit-modal-close');
    }

    public function deleteConversation(): void
    {
        $team = $this->currentTeamOrFail();
        $conversation = $this->activeConversationModel($team);

        abort_if(! $conversation, 404);
        abort_unless($this->canManageChat($team, $this->user()->id), 403);

        $conversation->messages()->delete();
        $conversation->participants()->detach();
        $conversation->delete();

        $this->conversation = (string) (TeamConversation::query()
            ->where('team_id', $team->id)
            ->latest('last_message_at')
            ->value('id') ?? '');

        $this->notifySuccess(__('Conversation deleted successfully.'));
    }

    public function approveReview(int $reviewId): void
    {
        $team = $this->currentTeamOrFail();
        abort_unless($this->canApprovePosts($team, $this->user()->id), 403);

        $review = TeamPostReview::query()
            ->where('team_id', $team->id)
            ->where('status', 'pending')
            ->findOrFail($reviewId);

        $requestedStatus = (int) ($review->metadata['requested_status'] ?? PublishingPost::STATUS_SCHEDULED);

        $review->post?->update([
            'status' => $requestedStatus,
            'changed' => now()->timestamp,
        ]);

        if (
            $review->post
            && $requestedStatus === PublishingPost::STATUS_SCHEDULED
            && (string) ($review->metadata['schedule_mode'] ?? '') === 'immediately'
        ) {
            PublishScheduledPostJob::dispatchSync((int) $review->post->id);
        }

        $review->update([
            'status' => 'approved',
            'decided_by_user_id' => $this->user()->id,
            'decision_note' => trim((string) ($this->reviewNotes[$reviewId] ?? '')) ?: null,
            'decided_at' => now(),
        ]);

        unset($this->reviewNotes[$reviewId]);
        $this->notifySuccess(__('Approval item approved successfully.'));
    }

    public function rejectReview(int $reviewId): void
    {
        $team = $this->currentTeamOrFail();
        abort_unless($this->canApprovePosts($team, $this->user()->id), 403);

        $review = TeamPostReview::query()
            ->where('team_id', $team->id)
            ->where('status', 'pending')
            ->findOrFail($reviewId);

        $review->post?->update([
            'status' => PublishingPost::STATUS_DRAFT,
            'changed' => now()->timestamp,
        ]);

        $review->update([
            'status' => 'rejected',
            'decided_by_user_id' => $this->user()->id,
            'decision_note' => trim((string) ($this->reviewNotes[$reviewId] ?? '')) ?: null,
            'decided_at' => now(),
        ]);

        unset($this->reviewNotes[$reviewId]);
        $this->notifySuccess(__('Approval item rejected successfully.'));
    }

    public function render(): View
    {
        abort_unless($this->teamsEnabled(), 404);

        $user = $this->user();
        $accessibleTeams = $this->accessibleTeams($user->id);
        $team = $this->currentTeamModel($accessibleTeams);
        $pendingInvites = collect();
        $reviews = collect();
        $comments = collect();
        $conversations = collect();
        $activeConversation = null;

        if ($team) {
            $team->load([
                'owner',
                'members' => fn ($query) => $query->orderBy('name'),
            ]);

            if ($this->team_name === '' && $this->team_description === '') {
                $this->syncTeamState($team, $accessibleTeams);
            }

            $pendingInvites = TeamInvitation::query()
                ->where('team_id', $team->id)
                ->where('status', 'pending')
                ->latest()
                ->get()
                ->map(function (TeamInvitation $invite) {
                    $joinUrl = route('portal.teams.join', ['inviteCode' => $invite->invite_code]);
                    $invite->setAttribute('join_url', $joinUrl);
                    $invite->setAttribute('join_qr_svg', $this->makeQrCodeSvg($joinUrl));

                    return $invite;
                });

            $reviews = TeamPostReview::query()
                ->where('team_id', $team->id)
                ->latest('submitted_at')
                ->with(['post', 'submitter', 'decider'])
                ->limit(8)
                ->get();

            $postComments = TeamPostComment::query()
                ->where('team_id', $team->id)
                ->latest()
                ->with(['post', 'author'])
                ->limit(8)
                ->get();

            $comments = $reviews
                ->map(function (TeamPostReview $review) {
                    $postTitle = (string) ($review->post?->data['title'] ?? __('Untitled post'));
                    $submittedBy = $review->submitter?->name ?: __('Unknown');
                    $decidedBy = $review->decider?->name ?: null;
                    $status = (string) $review->status;
                    $note = trim((string) ($review->decision_note ?? ''));

                    return (object) [
                        'kind' => 'review',
                        'author_name' => $decidedBy ?: $submittedBy,
                        'title' => $postTitle,
                        'message' => $note !== ''
                            ? $note
                            : __('Review status changed to :status.', ['status' => str($status)->headline()]),
                        'meta' => $decidedBy
                            ? __(':status by :user', ['status' => str($status)->headline(), 'user' => $decidedBy])
                            : __('Submitted by :user', ['user' => $submittedBy]),
                        'created_at' => $review->decided_at ?: $review->submitted_at ?: $review->created_at,
                    ];
                })
                ->concat(
                    $postComments->map(function (TeamPostComment $comment) {
                        return (object) [
                            'kind' => 'comment',
                            'author_name' => $comment->author?->name ?: __('Unknown'),
                            'title' => (string) ($comment->post?->data['title'] ?? __('Untitled post')),
                            'message' => (string) $comment->message,
                            'meta' => __('Post comment'),
                            'created_at' => $comment->created_at,
                        ];
                    })
                )
                ->sortByDesc(fn ($item) => $item->created_at?->getTimestamp() ?? 0)
                ->take(8)
                ->values();

            $conversations = TeamConversation::query()
                ->where('team_id', $team->id)
                ->latest('last_message_at')
                ->withCount('messages')
                ->get();

            $activeConversation = $this->activeConversationModel($team);

            if ($activeConversation) {
                $activeConversation->load([
                    'participants' => fn ($query) => $query->orderBy('name'),
                    'messages' => fn ($query) => $query->with('author')->latest()->limit(40),
                ]);

                $activeConversation->setRelation(
                    'messages',
                    $activeConversation->messages->sortBy('created_at')->values()
                );
            }
        }

        return view('appteams::index', [
            'teamModel' => $team,
            'user' => $user,
            'accessibleTeams' => $accessibleTeams,
            'conversationTopicOptions' => $this->conversationTopicOptions(),
            'pendingInvites' => $pendingInvites,
            'reviews' => $reviews,
            'comments' => $comments,
            'conversations' => $conversations,
            'activeConversation' => $activeConversation,
            'canManageTeam' => $this->canManageTeam($team, $user->id),
            'canManageMembers' => $this->canManageMembers($team, $user->id),
            'canManageInvites' => $this->canManageInvites($team, $user->id),
            'canManageChat' => $this->canManageChat($team, $user->id),
            'canApprovePosts' => $this->canApprovePosts($team, $user->id),
            'canUseChat' => $this->canUseChat($team, $user->id),
            'teamPermissionLabels' => $this->availableTeamPermissions($team),
            'teamModuleLabels' => $this->availableTeamModules(),
            'workspaceAccounts' => $team && TeamWorkspaceAccess::teamHasModule($team, 'channels')
                ? SocialAccount::query()
                    ->where('created_by_user_id', (int) $team->owner_user_id)
                    ->orderBy('display_name')
                    ->get(['id', 'display_name', 'provider_key', 'username'])
                : collect(),
            'publishingEnabled' => TeamWorkspaceAccess::teamHasModule($team, 'publishing'),
            'chatEnabled' => TeamWorkspaceAccess::teamHasModule($team, 'chat'),
            'currentUserTeamRole' => $team ? $this->currentUserRole($team, $user->id) : null,
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Teams'),
        ]);
    }

    protected function initializeWorkspace(): void
    {
        $accessibleTeams = $this->accessibleTeams($this->user()->id);
        $this->syncTeamState(null, $accessibleTeams);
    }

    protected function syncTeamState(?Team $team = null, ?Collection $accessibleTeams = null): void
    {
        $accessibleTeams ??= $this->accessibleTeams($this->user()->id);
        $team ??= $this->currentTeamModel($accessibleTeams);

        if (! $team) {
            $this->team = '';
            $this->conversation = '';
            $this->team_name = '';
            $this->team_description = '';
            $this->enabled_modules = [];

            return;
        }

        $this->team = (string) $team->id;
        $this->team_name = (string) $team->name;
        $this->team_description = (string) ($team->description ?? '');
        $this->enabled_modules = TeamWorkspaceAccess::enabledModules($team);
        $this->invite_permissions = $this->defaultPermissionsForRole('member', $team);
        $this->inviteManagedAccounts = [];
        $this->syncMemberAccessState($team);
        session(['portal_team_id' => $team->id]);

        $conversationExists = TeamConversation::query()
            ->where('team_id', $team->id)
            ->when($this->conversation !== '', fn ($query) => $query->whereKey((int) $this->conversation))
            ->exists();

        if (! $conversationExists) {
            $this->conversation = (string) (TeamConversation::query()
                ->where('team_id', $team->id)
                ->latest('last_message_at')
                ->value('id') ?? '');
        }
    }

    protected function currentTeamOrFail(): Team
    {
        $team = $this->currentTeamModel();
        abort_if(! $team, 404);

        return $team;
    }

    protected function currentTeamModel(?Collection $accessibleTeams = null): ?Team
    {
        $accessibleTeams ??= $this->accessibleTeams($this->user()->id);

        if ($accessibleTeams->isEmpty()) {
            return null;
        }

        $requestedTeamId = (int) ($this->team !== '' ? $this->team : session('portal_team_id', 0));

        if ($requestedTeamId > 0) {
            $team = $accessibleTeams->firstWhere('id', $requestedTeamId);
            if ($team) {
                return $team;
            }
        }

        return $accessibleTeams->first();
    }

    protected function activeConversationModel(Team $team): ?TeamConversation
    {
        return TeamConversation::query()
            ->where('team_id', $team->id)
            ->when(
                $this->conversation !== '',
                fn ($query) => $query->whereKey((int) $this->conversation),
                fn ($query) => $query->latest('last_message_at')
            )
            ->first();
    }

    protected function accessibleTeams(?int $userId): Collection
    {
        if (! $userId) {
            return collect();
        }

        return Team::query()
            ->where(function ($query) use ($userId) {
                $query->where('owner_user_id', $userId)
                    ->orWhereHas('members', fn ($memberQuery) => $memberQuery->where('users.id', $userId));
            })
            ->orderByRaw('CASE WHEN owner_user_id = ? THEN 0 ELSE 1 END', [$userId])
            ->orderBy('name')
            ->get()
            ->unique('id')
            ->values();
    }

    protected function syncMemberAccessState(Team $team): void
    {
        $team->loadMissing([
            'members' => fn ($query) => $query->orderBy('name'),
        ]);

        $this->memberPermissions = $team->members
            ->mapWithKeys(fn ($member) => [(int) $member->id => (int) $team->owner_user_id === (int) $member->id
                ? $this->defaultPermissionsForRole('owner', $team)
                : $this->decodePermissions($member->pivot->permissions ?? null, (string) ($member->pivot->role ?? 'member'), $team)])
            ->all();

        $this->memberManagedAccounts = $team->members
            ->mapWithKeys(fn ($member) => [(int) $member->id => (int) $team->owner_user_id === (int) $member->id
                ? array_map('strval', $this->availableManagedAccountIds($team))
                : $this->decodeManagedAccountIds($member->pivot->managed_account_ids ?? null)])
            ->all();
    }

    protected function acceptInvite(User $user, string $inviteCode): mixed
    {
        $normalizedInviteCode = strtoupper(trim($inviteCode));

        $invitation = TeamInvitation::query()
            ->where('invite_code', $normalizedInviteCode)
            ->where('status', 'pending')
            ->first();

        if (! $invitation || ($invitation->expires_at && $invitation->expires_at->isPast())) {
            $this->addError('invite_code', __('This invite code is invalid or has expired.'));
            $this->invite_code = $normalizedInviteCode;

            return null;
        }

        if ($invitation->team->members()->where('users.id', $user->id)->exists()) {
            session(['portal_team_id' => $invitation->team_id]);
            $this->team = (string) $invitation->team_id;
            $this->notifySuccess(__('You are already a member of this team.'));

            return null;
        }

        if (! $this->canJoinTeamUnderCurrentPlan($user, $invitation->team)) {
            $this->addError('invite_code', __('This team has reached the maximum member limit for its current plan.'));
            $this->invite_code = $normalizedInviteCode;

            return null;
        }

        $acceptedRole = in_array($invitation->role, ['admin', 'editor', 'member'], true)
            ? $invitation->role
            : 'member';

        $invitation->team->members()->syncWithoutDetaching([
            $user->id => [
                'role' => $acceptedRole,
                'permissions' => $this->encodePermissions(
                    is_array($invitation->permissions) && $invitation->permissions !== []
                        ? $invitation->permissions
                        : $this->defaultPermissionsForRole($acceptedRole, $invitation->team)
                ),
                'managed_account_ids' => $this->encodeManagedAccountIds(
                    $this->filterManagedAccountIdsForTeam(
                        $this->decodeManagedAccountIds((array) data_get($invitation->metadata, 'managed_account_ids', [])),
                        $invitation->team,
                    )
                ),
            ],
        ]);

        $invitation->update([
            'status' => 'accepted',
            'accepted_by_user_id' => $user->id,
            'accepted_at' => now(),
        ]);

        session(['portal_team_id' => $invitation->team_id]);

        return $this->redirectRoute('portal.teams', ['team' => $invitation->team_id], navigate: true);
    }

    protected function canManageTeam(?Team $team, ?int $userId): bool
    {
        return $this->hasTeamPermission($team, $userId, 'team.manage');
    }

    protected function canManageMembers(?Team $team, ?int $userId): bool
    {
        return $this->hasTeamPermission($team, $userId, 'member.manage');
    }

    protected function canManageInvites(?Team $team, ?int $userId): bool
    {
        return $this->isTeamOwner($team, $userId);
    }

    protected function canApprovePosts(?Team $team, ?int $userId): bool
    {
        return $this->hasTeamPermission($team, $userId, 'post.approve');
    }

    protected function canManageChat(?Team $team, ?int $userId): bool
    {
        return $this->hasTeamPermission($team, $userId, 'chat.manage');
    }

    protected function canUseChat(?Team $team, ?int $userId): bool
    {
        if (! $team || ! $userId) {
            return false;
        }

        if (! TeamWorkspaceAccess::teamHasModule($team, 'chat')) {
            return false;
        }

        return $this->hasTeamPermission($team, $userId, 'chat.manage')
            || $this->hasTeamPermission($team, $userId, 'chat.participate');
    }

    protected function canAccessConversation(TeamConversation $conversation, int $userId): bool
    {
        return $conversation->participants()->where('users.id', $userId)->exists()
            || $conversation->team()->where('owner_user_id', $userId)->exists();
    }

    protected function defaultPermissionsForRole(string $role, ?Team $team = null): array
    {
        if ($role === 'owner') {
            return array_keys($this->availableTeamPermissions($team));
        }

        return TeamPermissionRegistry::defaultPermissionsForRole($role, TeamWorkspaceAccess::enabledModules($team));
    }

    protected function availableTeamPermissions(?Team $team = null): array
    {
        return TeamPermissionRegistry::permissionLabels(TeamWorkspaceAccess::enabledModules($team));
    }

    protected function availableTeamModules(): array
    {
        return TeamPermissionRegistry::moduleLabels(true);
    }

    protected function conversationTopicOptions(): array
    {
        return [
            __('Content planning') => __('Content planning'),
            __('Campaign coordination') => __('Campaign coordination'),
            __('Post approvals') => __('Post approvals'),
            __('Publishing schedule') => __('Publishing schedule'),
            __('Customer support') => __('Customer support'),
            __('General discussion') => __('General discussion'),
        ];
    }

    protected function hasTeamPermission(?Team $team, ?int $userId, string $permission): bool
    {
        if (! $team || ! $userId) {
            return false;
        }

        $user = User::query()->find($userId);

        return TeamWorkspaceAccess::hasPermission($user, $permission, $team);
    }

    protected function permissionsForTeamMember(Team $team, int $userId): array
    {
        $user = User::query()->find($userId);

        return TeamWorkspaceAccess::permissionsForUser($user, $team);
    }

    protected function currentUserRole(Team $team, int $userId): string
    {
        if ((int) $team->owner_user_id === $userId) {
            return 'owner';
        }

        return (string) ($team->members->firstWhere('id', $userId)?->pivot->role
            ?? $team->members()->where('users.id', $userId)->first()?->pivot?->role
            ?? 'member');
    }

    protected function isTeamOwner(?Team $team, ?int $userId): bool
    {
        return $team && $userId && (int) $team->owner_user_id === (int) $userId;
    }

    protected function decodePermissions(mixed $permissions, string $role = 'member', ?Team $team = null): array
    {
        $decodedPermissions = [];

        if (is_array($permissions)) {
            $decodedPermissions = collect($permissions)->filter()->map(fn ($permission) => (string) $permission)->unique()->values()->all();
        }

        if ($decodedPermissions === [] && is_string($permissions) && trim($permissions) !== '') {
            $decoded = json_decode($permissions, true);

            if (is_array($decoded)) {
                $decodedPermissions = collect($decoded)->filter()->map(fn ($permission) => (string) $permission)->unique()->values()->all();
            }
        }

        $knownPermissions = collect($decodedPermissions)
            ->filter(fn ($permission) => TeamPermissionRegistry::hasPermission((string) $permission))
            ->map(fn ($permission) => (string) $permission)
            ->values()
            ->all();
        $containsLegacyPermissions = count($knownPermissions) !== count($decodedPermissions);
        $defaultPermissions = $this->filterPermissionsForEnabledModules($this->defaultPermissionsForRole($role, $team), $team);

        if ($knownPermissions === []) {
            return $defaultPermissions;
        }

        if ($containsLegacyPermissions) {
            return collect(array_merge($defaultPermissions, $knownPermissions))
                ->filter(fn ($permission) => $this->permissionEnabledForTeam((string) $permission, $team))
                ->unique()
                ->values()
                ->all();
        }

        return collect($knownPermissions)
            ->filter(fn ($permission) => $this->permissionEnabledForTeam((string) $permission, $team))
            ->unique()
            ->values()
            ->all();
    }

    protected function filterPermissionsForEnabledModules(array $permissions, ?Team $team): array
    {
        return collect($permissions)
            ->filter(fn ($permission) => $this->permissionEnabledForTeam((string) $permission, $team))
            ->map(fn ($permission) => (string) $permission)
            ->unique()
            ->values()
            ->all();
    }

    protected function permissionEnabledForTeam(string $permission, ?Team $team): bool
    {
        $module = TeamPermissionRegistry::permissionModule($permission);

        return $module ? TeamWorkspaceAccess::teamHasModule($team, $module) : true;
    }

    protected function encodePermissions(?array $permissions): ?string
    {
        if ($permissions === null) {
            return null;
        }

        return json_encode(array_values($permissions), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    protected function decodeManagedAccountIds(mixed $managedAccountIds): array
    {
        if (is_array($managedAccountIds)) {
            return collect($managedAccountIds)
                ->map(fn ($id) => (string) (int) $id)
                ->filter(fn ($id) => (int) $id > 0)
                ->unique()
                ->values()
                ->all();
        }

        if (is_string($managedAccountIds) && trim($managedAccountIds) !== '') {
            $decoded = json_decode($managedAccountIds, true);

            if (is_array($decoded)) {
                return collect($decoded)
                    ->map(fn ($id) => (string) (int) $id)
                    ->filter(fn ($id) => (int) $id > 0)
                    ->unique()
                    ->values()
                    ->all();
            }
        }

        return [];
    }

    protected function encodeManagedAccountIds(?array $managedAccountIds): ?string
    {
        if ($managedAccountIds === null) {
            return null;
        }

        return json_encode(array_values($managedAccountIds), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    protected function filterManagedAccountIdsForTeam(array $managedAccountIds, Team $team): array
    {
        $availableAccountIds = $this->availableManagedAccountIds($team);

        return collect($managedAccountIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0 && in_array($id, $availableAccountIds, true))
            ->unique()
            ->values()
            ->all();
    }

    protected function availableManagedAccountIds(Team $team): array
    {
        return SocialAccount::query()
            ->where('created_by_user_id', (int) $team->owner_user_id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    protected function makeQrCodeSvg(string $value): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(
                220,
                2,
                null,
                null,
                Fill::uniformColor(new Rgb(15, 23, 42), new Rgb(255, 255, 255))
            ),
            new SvgImageBackEnd()
        );

        return (new Writer($renderer))->writeString($value);
    }

    protected function notifySuccess(string $message): void
    {
        $this->statusMessage = $message;
        $this->errorMessage = null;
        $this->dispatch('app-toast', type: 'success', message: $message);
    }

    protected function notifyError(string $message): void
    {
        $this->errorMessage = $message;
        $this->statusMessage = null;
        $this->dispatch('app-toast', type: 'error', message: $message);
    }

    protected function user(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }

    protected function teamsEnabled(?Team $team = null): bool
    {
        $planUser = $this->teamsPlanUser($team);

        if (! $planUser?->plan) {
            return true;
        }

        return $planUser->hasPlanFeature('teams') || $planUser->hasPlanFeature('teams_feature');
    }

    protected function maxTeamMembers(?Team $team = null): ?int
    {
        $limit = (int) ($this->teamsPlanUser($team)?->planLimit('max_team_members', -1) ?? -1);

        return $limit < 0 ? null : $limit;
    }

    protected function hasReachedTeamMemberLimit(Team $team): bool
    {
        $limit = $this->maxTeamMembers($team);

        if ($limit === null) {
            return false;
        }

        return $team->members()->count() >= $limit;
    }

    protected function canJoinTeamUnderCurrentPlan(User $user, Team $team): bool
    {
        $planUser = $this->teamsPlanUser($team) ?: $user;

        if ($planUser?->plan && ! ($planUser->hasPlanFeature('teams') || $planUser->hasPlanFeature('teams_feature'))) {
            return false;
        }

        $limit = (int) ($planUser?->planLimit('max_team_members', -1) ?? -1);

        if ($limit < 0) {
            return true;
        }

        return $team->members()->count() < $limit;
    }

    protected function teamsPlanUser(?Team $team = null): ?User
    {
        $user = auth()->user();
        $team ??= $this->currentTeamModel($user ? $this->accessibleTeams($user->id) : collect());
        $team ??= TeamWorkspaceAccess::activeTeam($user);

        return $team?->owner ?: $user;
    }
}
