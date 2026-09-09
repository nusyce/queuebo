<?php

namespace Modules\AppBulkPosts\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\AdminUser\Models\User;
use Modules\AppBulkPosts\Jobs\ProcessBulkPostBatchJob;
use Modules\AppBulkPosts\Models\BulkPostBatch;
use Modules\AppBulkPosts\Support\BulkPostCsvService;
use Modules\AppChannels\Models\SocialAccount;
use Modules\AppPublishing\Models\PublishingCampaign;
use Modules\AppPublishing\Models\PublishingLabel;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

class AppBulkPostController extends Controller
{
    public function __construct(
        protected BulkPostCsvService $csvService,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($this->bulkPostsEnabled($request->user()), 404);

        $user = $request->user();
        $accountQuery = $this->publishableAccountsQuery($user);
        $workspaceOwnerUserId = TeamWorkspaceAccess::workspaceOwnerUserId($user);

        $selectedBatch = $request->integer('batch')
            ? BulkPostBatch::query()
                ->ownedBy((int) $user->id)
                ->with(['rows' => fn ($query) => $query->orderBy('row_number')->limit(25)])
                ->find($request->integer('batch'))
            : null;

        return view('appbulkposts::index', [
            'flowSteps' => $this->csvService->flowSteps(),
            'csvColumns' => $this->csvService->csvColumns(),
            'batches' => BulkPostBatch::query()
                ->ownedBy((int) $user->id)
                ->withCount('rows')
                ->latest('id')
                ->limit(8)
                ->get(),
            'selectedBatch' => $selectedBatch,
            'accounts' => (clone $accountQuery)->orderBy('display_name')->get(['id', 'display_name', 'username', 'provider_key', 'category', 'avatar_url']),
            'campaigns' => PublishingCampaign::query()
                ->ownedBy($workspaceOwnerUserId)
                ->orderBy('name')
                ->get(['id', 'name', 'status']),
            'labels' => PublishingLabel::query()
                ->ownedBy($workspaceOwnerUserId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'stats' => [
                'accounts' => (clone $accountQuery)->count(),
                'batches' => BulkPostBatch::query()->ownedBy((int) $user->id)->count(),
                'rows' => BulkPostBatch::query()->ownedBy((int) $user->id)->withCount('rows')->get()->sum('rows_count'),
                'ready_accounts' => SocialAccount::query()
                    ->whereIn('id', (clone $accountQuery)->pluck('id')->all())
                    ->where('is_active', true)
                    ->count(),
            ],
            'canShortenUrls' => url_shortening_enabled($user),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->bulkPostsEnabled($request->user()), 404);

        $validated = $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
            'interval_minutes' => ['nullable', 'integer', 'min:1'],
            'account_ids' => ['required', 'array', 'min:1'],
            'account_ids.*' => ['integer'],
            'campaign_id' => ['nullable', 'integer'],
            'label_ids' => ['nullable', 'array'],
            'label_ids.*' => ['integer'],
            'shorten_urls' => ['nullable', 'in:0,1'],
        ]);

        $workspaceOwnerUserId = TeamWorkspaceAccess::workspaceOwnerUserId($request->user());
        $campaignId = filled($validated['campaign_id'] ?? null)
            ? PublishingCampaign::query()->ownedBy($workspaceOwnerUserId)->find((int) $validated['campaign_id'])?->id
            : null;
        $labelIds = PublishingLabel::query()
            ->ownedBy($workspaceOwnerUserId)
            ->whereIn('id', collect((array) ($validated['label_ids'] ?? []))->map(fn ($id) => (int) $id)->filter()->all())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
        $accountIds = $this->publishableAccountsQuery($request->user())
            ->whereIn('id', collect((array) $validated['account_ids'])->map(fn ($id) => (int) $id)->all())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $batch = $this->csvService->import($validated['csv_file'], $request->user(), [
            'account_ids' => $accountIds,
            'interval_minutes' => $validated['interval_minutes'] ?? 60,
            'campaign_id' => $campaignId,
            'label_ids' => $labelIds,
            'shorten_urls' => url_shortening_enabled($request->user()) && (string) ($validated['shorten_urls'] ?? '0') === '1',
        ]);
        ProcessBulkPostBatchJob::dispatchSync($batch->id);
        $batch->refresh();
        $createdPosts = (int) data_get($batch->stats, 'created_posts', 0);
        $failedRows = (int) data_get($batch->stats, 'failed_rows', 0);

        return redirect()
            ->route('portal.publishing.calendar')
            ->with('status', __('Bulk posts have been added to Publishing. Success: :success, Failed: :failed.', [
                'success' => number_format($createdPosts),
                'failed' => number_format($failedRows),
            ]));
    }

    public function process(Request $request, BulkPostBatch $batch): RedirectResponse
    {
        abort_unless($this->bulkPostsEnabled($request->user()), 404);
        abort_unless((int) $batch->owner_user_id === (int) $request->user()->id, 404);

        ProcessBulkPostBatchJob::dispatch($batch->id);

        return redirect()
            ->route('portal.bulk-posts', ['batch' => $batch->id])
            ->with('status', __('Bulk post batch has been queued for processing.'));
    }

    public function template(): Response
    {
        abort_unless($this->bulkPostsEnabled(auth()->user()), 404);

        $content = collect($this->csvService->templateRows())
            ->map(fn (array $row) => implode(',', array_map(function ($value) {
                $value = (string) $value;

                if (str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")) {
                    return '"'.str_replace('"', '""', $value).'"';
                }

                return $value;
            }, $row)))
            ->implode("\n");

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="bulk-posts-template.csv"',
        ]);
    }

    protected function publishableAccountsQuery(?User $user)
    {
        $publishableCapabilityKeys = publishable_channel_capability_keys($user);

        return TeamWorkspaceAccess::accessibleAccountsQuery($user)
            ->where(function ($query) use ($publishableCapabilityKeys): void {
                $query->whereIn('capability_key', $publishableCapabilityKeys)
                    ->orWhere(function ($fallbackQuery) use ($publishableCapabilityKeys): void {
                        $fallbackQuery->whereNull('capability_key')
                            ->whereIn('provider_key', $publishableCapabilityKeys);
                    });
            });
    }

    protected function bulkPostsEnabled(?User $user): bool
    {
        $team = TeamWorkspaceAccess::activeTeam($user);
        $planOwner = $team?->owner ?: $user;

        if (! TeamWorkspaceAccess::teamHasModule($team, 'bulk_posts')) {
            return false;
        }

        if (! TeamWorkspaceAccess::hasPermission($user, 'bulk_posts.view', $team)) {
            return false;
        }

        return ! $planOwner?->plan || $planOwner->hasPlanFeature('bulk_posts');
    }
}
