<?php

namespace Modules\AppAiPublishing\Console\Commands;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\AppAiPublishing\Jobs\ProcessAiPublishingRunJob;
use Modules\AppAiPublishing\Models\AiPublishingRun;
use Modules\AppAiPublishing\Support\AiPublishingScheduler;

class DispatchQueuedAiPublishingRunsCommand extends Command
{
    protected $signature = 'ai-publishing:dispatch-queued {--limit=25 : Maximum number of queued AI publishing runs to dispatch per run}';

    protected $description = 'Dispatch queued AI publishing runs so they can generate publishing posts.';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $queuedRuns = AiPublishingRun::query()
            ->where('status', 'queued')
            ->orderBy('id')
            ->limit($limit)
            ->get(['id', 'schedule_config', 'last_processed_at']);

        $queuedRuns = $queuedRuns
            ->filter(fn (AiPublishingRun $run) => $this->isRunDue($run))
            ->values();

        if ($queuedRuns->isEmpty()) {
            $this->line('No queued AI publishing runs found.');

            return self::SUCCESS;
        }

        foreach ($queuedRuns as $run) {
            $claimed = AiPublishingRun::query()
                ->whereKey($run->id)
                ->where('status', 'queued')
                ->update([
                    'status' => 'processing',
                    'updated_at' => now(),
                ]);

            if ($claimed !== 1) {
                continue;
            }

            try {
                app()->call([(new ProcessAiPublishingRunJob((int) $run->id)), 'handle']);
            } catch (\Throwable $exception) {
                AiPublishingRun::query()
                    ->whereKey($run->id)
                    ->update([
                        'status' => 'failed',
                        'stats->last_error_message' => trim($exception->getMessage()),
                        'last_processed_at' => now(),
                        'updated_at' => now(),
                    ]);

                Log::error('AI publishing run failed during scheduled dispatch.', [
                    'run_id' => (int) $run->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        $this->info(sprintf('Processed up to %d queued AI publishing run(s).', $queuedRuns->count()));

        return self::SUCCESS;
    }

    protected function isRunDue(AiPublishingRun $run): bool
    {
        $nextSlot = $this->nextDueSlot($run);

        if (! $nextSlot) {
            return false;
        }

        $timezone = (string) data_get($run->schedule_config, 'timezone', config('app.timezone'));

        return $nextSlot->lessThanOrEqualTo(now($timezone));
    }

    protected function nextDueSlot(AiPublishingRun $run): ?CarbonInterface
    {
        $config = (array) ($run->schedule_config ?? []);
        $timezone = (string) data_get($config, 'timezone', config('app.timezone'));

        return app(AiPublishingScheduler::class)->nextDueSlot(
            $config,
            $timezone,
            $run->last_processed_at ? Carbon::parse($run->last_processed_at)->timezone($timezone) : null
        );
    }
}
