<?php

namespace Modules\AppAutomation\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AppAutomation\Services\AutomationApiKeyService;
use Modules\AppAutomation\Services\AutomationWebhookDispatcher;
use Modules\AppAutomation\Support\AutomationAccess;
use Modules\AppTeams\Support\TeamPermissionRegistry;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

class AppAutomationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appautomation');
        $this->app->singleton(AutomationAccess::class);
        $this->app->singleton(AutomationApiKeyService::class);
        $this->app->singleton(AutomationWebhookDispatcher::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appautomation');

        TeamPermissionRegistry::registerModule('automation', [
            'label' => __('Automation API'),
            'configurable' => true,
            'enabled_by_default' => true,
        ]);

        register_plan_permission([
            'key' => 'automation',
            'label' => __('Automation'),
            'type' => 'config',
            'order' => 190,
            'fields' => [
                [
                    'key' => 'max_automation_api_keys',
                    'label' => __('Max automation API keys'),
                    'type' => 'number',
                    'default' => 10,
                ],
                [
                    'key' => 'max_automation_webhooks',
                    'label' => __('Max automation webhooks'),
                    'type' => 'number',
                    'default' => 10,
                ],
            ],
        ]);

        register_user_sidebar_item('workspace', [
            'label' => 'Automation API',
            'route_name' => 'portal.automation.index',
            'active_when' => ['portal.automation.*'],
            'icon' => 'fa-light fa-bolt',
            'order' => 70,
            'visible' => function (): bool {
                $user = auth()->user();
                $team = TeamWorkspaceAccess::activeTeam($user);
                $planOwner = $team?->owner ?: $user;

                if (! TeamWorkspaceAccess::teamHasModule($team, 'automation')) {
                    return false;
                }

                return $planOwner?->canUsePlanFeature('automation') ?? false;
            },
        ]);

        $this->app->booted(function (): void {
            \Pricing::addSubFeatures([
                ['sort' => 135, 'parent' => 'features', 'tab_id' => 'workspace', 'tab_name' => __('Workspace'), 'key' => 'automation', 'label' => __('Automation'), 'check' => true, 'type' => 'boolean', 'raw' => 0],
                ['sort' => 136, 'parent' => 'features', 'tab_id' => 'workspace', 'tab_name' => __('Workspace'), 'key' => 'max_automation_api_keys', 'label' => __('Automation API keys'), 'check' => true, 'type' => 'number', 'raw' => 0],
                ['sort' => 137, 'parent' => 'features', 'tab_id' => 'workspace', 'tab_name' => __('Workspace'), 'key' => 'max_automation_webhooks', 'label' => __('Automation webhooks'), 'check' => true, 'type' => 'number', 'raw' => 0],
            ]);
        });
    }
}
