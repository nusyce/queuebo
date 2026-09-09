<?php

namespace Modules\AppAutomation\Support;

use Modules\AdminUser\Models\User;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

class AutomationAccess
{
    public function enabled(?User $user): bool
    {
        $team = TeamWorkspaceAccess::activeTeam($user);
        $planOwner = $team?->owner ?: $user;

        if (! TeamWorkspaceAccess::teamHasModule($team, 'automation')) {
            return false;
        }

        return ! $planOwner?->plan || $planOwner->hasPlanFeature('automation');
    }

    public function workspaceOwnerUserId(?User $user): int
    {
        return TeamWorkspaceAccess::workspaceOwnerUserId($user);
    }

    public function teamId(?User $user): ?int
    {
        return TeamWorkspaceAccess::activeTeam($user)?->id;
    }
}
