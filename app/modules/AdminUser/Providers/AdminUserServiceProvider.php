<?php

namespace Modules\AdminUser\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AdminUserServiceProvider extends ServiceProvider
{
    /**
     * Register module services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.adminuser');
    }

    /**
     * Bootstrap module services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'adminuser');

        register_setting_item('general', [
            'label' => 'Authentication Rules',
            'description' => 'Control sign-up, account changes, and social sign-in providers.',
            'route_name' => 'settings.auth',
            'active_when' => ['settings.auth'],
            'order' => 14,
        ]);

        register_sidebar_section('ai-settings', 'AI Center', 20);
        register_sidebar_section('user-portal', 'User Portal', 30);

        register_sidebar_item('user-portal', [
            'label' => 'Users',
            'route_name' => 'admin-users.index',
            'active_when' => ['admin-users.*'],
            'icon' => 'fa-light fa-user-group',
            'order' => 10,
            'permission' => 'admin-users.view',
        ]);

        register_sidebar_item('user-portal', [
            'label' => 'Teams',
            'route_name' => 'admin-user-teams.index',
            'active_when' => ['admin-user-teams.*'],
            'icon' => 'fa-light fa-people-group',
            'order' => 20,
            'permission' => 'admin-user-teams.view',
        ]);

        register_sidebar_item('user-portal', [
            'label' => 'User Report',
            'route_name' => 'admin-user-report.index',
            'active_when' => ['admin-user-report.*'],
            'icon' => 'fa-light fa-chart-column',
            'order' => 30,
            'permission' => 'admin-user-report.view',
        ]);

        register_sidebar_item('user-portal', [
            'label' => 'User Logs',
            'route_name' => 'admin-user-logs.index',
            'active_when' => ['admin-user-logs.*'],
            'icon' => 'fa-light fa-clipboard-list-check',
            'order' => 35,
            'permission' => 'admin-user-logs.view',
        ]);

        register_sidebar_item('user-portal', [
            'label' => 'User Roles',
            'route_name' => 'admin-user-roles.index',
            'active_when' => ['admin-user-roles.*'],
            'icon' => 'fa-light fa-user-shield',
            'order' => 40,
            'permission' => 'admin-user-roles.view',
        ]);

        register_user_sidebar_section('overview', 'Overview', 10);
        register_user_sidebar_section('workspace', 'Workspace', 20);
        register_user_sidebar_section('content-tools', 'Content Tools', 30);
        register_user_sidebar_section('library', 'Library', 40);
        register_user_sidebar_section('help-desk', 'Help Desk', 50);
        register_user_sidebar_section('portal', 'Apps', 90);

        register_user_sidebar_item('overview', [
            'label' => 'Dashboard',
            'route_name' => 'portal.dashboard',
            'active_when' => ['portal.dashboard'],
            'icon' => 'dashboard',
            'order' => 10,
        ]);

        register_admin_dashboard_item('admin-users.snapshot', [
            'title' => 'Users',
            'view' => 'adminuser::dashboard.snapshot',
            'width' => 'full',
            'order' => 20,
            'data' => fn () => [
                'signupReport' => (function () {
                    $query = \Modules\AdminUser\Models\User::query();
                    $today = now()->startOfDay();
                    $weekStart = now()->subDays(6)->startOfDay();

                    $daily = collect(range(6, 0))->map(function (int $offset) use ($query) {
                        $date = now()->subDays($offset)->startOfDay();

                        return [
                            'label' => $date->format('d M'),
                            'value' => (clone $query)
                                ->whereBetween('created_at', [$date, (clone $date)->endOfDay()])
                                ->count(),
                        ];
                    })->values();

                    return [
                        'today' => (clone $query)->whereBetween('created_at', [$today, (clone $today)->endOfDay()])->count(),
                        'week' => (clone $query)->where('created_at', '>=', $weekStart)->count(),
                        'month' => (clone $query)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
                        'total' => (clone $query)->count(),
                        'daily' => $daily,
                    ];
                })(),
                'metrics' => [
                    'users' => \Modules\AdminUser\Models\User::query()->count(),
                    'teams' => \Modules\AdminUser\Models\Team::query()->count(),
                    'roles' => \Modules\AdminUser\Models\AdminRole::query()->count(),
                    'new_users' => \Modules\AdminUser\Models\User::query()->where('created_at', '>=', now()->subDays(7))->count(),
                ],
                'route' => route('admin-users.index'),
                'reportRoute' => route('admin-user-report.index'),
            ],
        ]);

        register_user_dashboard_item('admin-user.activity-summary', [
            'title' => 'Activity',
            'view' => 'adminuser::dashboard.user-activity-summary',
            'width' => 'full',
            'order' => 40,
            'route_name' => 'portal.activity',
            'data' => fn ($user) => (function () use ($user) {
                $empty = [
                    'logs' => collect(),
                    'metrics' => [
                        'loaded' => 0,
                        'today' => 0,
                        'week' => 0,
                        'areas' => 0,
                        'top_module' => __('No activity yet'),
                    ],
                    'route' => route('portal.activity'),
                ];

                if (! $user) {
                    return $empty;
                }

                $formatMetadataValue = static function (mixed $value): string {
                    if (is_array($value)) {
                        return collect($value)
                            ->take(3)
                            ->map(fn (mixed $nestedValue, mixed $nestedKey) => is_string($nestedKey)
                                ? $nestedKey.': '.(is_scalar($nestedValue) || $nestedValue === null ? (string) $nestedValue : json_encode($nestedValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                                : (is_scalar($nestedValue) || $nestedValue === null ? (string) $nestedValue : json_encode($nestedValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)))
                            ->implode(', ');
                    }

                    if (is_object($value)) {
                        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                        return $json !== false ? $json : '[object]';
                    }

                    if (is_bool($value)) {
                        return $value ? 'true' : 'false';
                    }

                    return $value === null ? '-' : (string) $value;
                };

                $resolveSurface = static function (?string $event, ?string $routeName): array {
                    $source = $routeName ?: $event ?: '';
                    $parts = array_values(array_filter(explode('.', (string) $source)));

                    if ($parts === []) {
                        return [__('General'), __('Updated')];
                    }

                    if (in_array($parts[0], ['portal', 'admin'], true)) {
                        array_shift($parts);
                    }

                    $action = Str::headline(array_pop($parts) ?: 'updated');
                    $module = Str::headline(str_replace('-', ' ', implode(' ', $parts ?: ['general'])));

                    return [$module, $action];
                };

                $baseQuery = \Modules\AdminUser\Models\AuditLog::query()
                    ->where('causer_user_id', $user->id);

                $latestLogs = (clone $baseQuery)
                    ->latest()
                    ->limit(6)
                    ->get();

                $summaryLogs = (clone $baseQuery)
                    ->latest()
                    ->limit(24)
                    ->get();

                $moduleCounts = $summaryLogs
                    ->map(function ($log) use ($resolveSurface) {
                        [$module] = $resolveSurface($log->event, $log->route_name);

                        return $module;
                    })
                    ->countBy();

                return [
                    'logs' => $latestLogs->map(function ($log) use ($formatMetadataValue, $resolveSurface) {
                        [$module, $action] = $resolveSurface($log->event, $log->route_name);

                        $metadataSummary = collect($log->metadata ?? [])
                            ->filter(fn ($value) => ! blank($value))
                            ->take(3)
                            ->map(fn ($value, $key) => Str::headline((string) $key).': '.$formatMetadataValue($value))
                            ->values();

                        return (object) [
                            'id' => $log->id,
                            'module' => $module,
                            'action' => $action,
                            'description' => $log->description ?: __('No description provided.'),
                            'area' => $log->area,
                            'area_label' => $log->area === 'user' ? __('Portal') : __('System'),
                            'area_variant' => $log->area === 'user' ? 'primary' : 'neutral',
                            'metadata_summary' => $metadataSummary,
                            'subject_label' => $log->subject_type ? Str::headline(class_basename($log->subject_type)) : null,
                            'created_at_label' => $log->created_at?->format('Y-m-d H:i'),
                            'created_at_relative' => $log->created_at?->diffForHumans(),
                        ];
                    }),
                    'metrics' => [
                        'loaded' => $latestLogs->count(),
                        'today' => (clone $baseQuery)->where('created_at', '>=', now()->startOfDay())->count(),
                        'week' => (clone $baseQuery)->where('created_at', '>=', now()->subDays(7))->count(),
                        'areas' => $summaryLogs->pluck('area')->filter()->unique()->count(),
                        'top_module' => $moduleCounts->sortDesc()->keys()->first() ?: __('No activity yet'),
                    ],
                    'route' => route('portal.activity'),
                ];
            })(),
            'visible' => fn ($user) => $user !== null,
        ]);
    }
}
