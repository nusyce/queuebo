<?php

namespace Modules\AppRssSchedules\Console\Commands;

use Illuminate\Console\Command;
use Modules\AppRssSchedules\Models\RssSchedule;
use Modules\AppRssSchedules\Services\RssScheduleService;

class DispatchQueuedRssSchedulesCommand extends Command
{
    protected $signature = 'rss-schedules:dispatch-queued {--schedule=} {--force}';

    protected $description = 'Fetch due RSS schedules and queue them into the publishing posts table.';

    public function handle(RssScheduleService $service): int
    {
        $query = RssSchedule::query()->where('status', true);

        if ($schedule = $this->option('schedule')) {
            $query->where(function ($builder) use ($schedule): void {
                $builder->where('id', $schedule)->orWhere('id_secure', $schedule);
            });
        }

        $now = now()->timestamp;

        if (! $this->option('force')) {
            $query->where(function ($builder) use ($now): void {
                $builder->whereNull('next_run_at')->orWhere('next_run_at', '<=', $now);
            });
        }

        $schedules = $query->orderBy('next_run_at')->get();

        if ($schedules->isEmpty()) {
            $this->line('No due RSS schedules found.');

            return self::SUCCESS;
        }

        foreach ($schedules as $rssSchedule) {
            $result = $service->processSchedule($rssSchedule, (bool) $this->option('force'));

            $this->line(sprintf(
                '%s: queued %d, skipped %d. %s',
                $rssSchedule->name,
                $result['queued'] ?? 0,
                $result['skipped'] ?? 0,
                $result['message'] ?? ''
            ));
        }

        return self::SUCCESS;
    }
}
