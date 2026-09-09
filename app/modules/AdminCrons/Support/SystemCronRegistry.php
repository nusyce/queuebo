<?php

namespace Modules\AdminCrons\Support;

use Illuminate\Support\Facades\Artisan;
use InvalidArgumentException;

class SystemCronRegistry
{
    protected array $registeredTasks = [];

    public function tasks(): array
    {
        return collect([
            [
                'key' => 'scheduler',
                'name' => __('Laravel Scheduler'),
                'icon' => 'fa-light fa-timer',
                'description' => __('Recommended main cron. It runs the Laravel scheduler and lets the app dispatch every scheduled command internally.'),
                'command' => 'schedule:run',
                'recommended' => true,
            ],
        ])
            ->merge($this->registeredTasks)
            ->unique('key')
            ->values()
            ->all();
    }

    public function register(array $task): self
    {
        if (! isset($task['key'], $task['name'], $task['command'])) {
            throw new InvalidArgumentException(__('Cron task definitions require key, name, and command.'));
        }

        $this->registeredTasks[] = $task;

        return $this;
    }

    public function find(string $key): array
    {
        $task = collect($this->tasks())->firstWhere('key', $key);

        if (! $task) {
            throw new InvalidArgumentException(__('Unknown cron task.'));
        }

        return $task;
    }

    public function execute(string $key): array
    {
        $task = $this->find($key);
        $exitCode = Artisan::call($task['command']);

        return [
            'task' => $task,
            'exit_code' => $exitCode,
            'output' => trim(Artisan::output()),
        ];
    }
}
