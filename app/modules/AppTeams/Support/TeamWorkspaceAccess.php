<?php

namespace Modules\AppTeams\Support;

use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\AppChannels\Models\SocialAccount;

class TeamWorkspaceAccess
{
    public static function activeTeam(?User $user): ?Team
    {
        if (! $user) {
            return null;
        }

        $teamId = (int) session('portal_team_id', 0);

        if ($teamId <= 0) {
            return null;
        }

        return Team::query()
            ->whereKey($teamId)
            ->where(function ($query) use ($user) {
                $query->where('owner_user_id', $user->id)
                    ->orWhereHas('members', fn ($memberQuery) => $memberQuery->where('users.id', $user->id));
            })
            ->first();
    }

    public static function workspaceOwnerUserId(?User $user): int
    {
        if (! $user) {
            return 0;
        }

        $team = self::activeTeam($user);

        if ($team && (int) $team->owner_user_id > 0) {
            return (int) $team->owner_user_id;
        }

        return (int) $user->id;
    }

    public static function enabledModules(?Team $team): array
    {
        $modules = $team?->enabled_modules;
        $alwaysEnabledModules = TeamPermissionRegistry::alwaysEnabledModuleKeys();

        // `null` means the team has never saved module preferences, so fall back
        // to registry defaults. An explicit empty array means "all configurable
        // modules disabled" and must be preserved.
        if (! is_array($modules)) {
            return collect(array_merge(
                TeamPermissionRegistry::defaultEnabledModuleKeys(),
                $alwaysEnabledModules
            ))
                ->unique()
                ->values()
                ->all();
        }

        return collect(array_merge($modules, $alwaysEnabledModules))
            ->filter()
            ->map(fn ($module) => (string) $module)
            ->unique()
            ->values()
            ->all();
    }

    public static function teamHasModule(?Team $team, string $module): bool
    {
        if (! $team) {
            return true;
        }

        if (self::userBypassesWorkspaceRestrictions(auth()->user(), $team)) {
            return true;
        }

        return in_array($module, self::enabledModules($team), true);
    }

    /**
     * @return array<int, string>
     */
    public static function permissionsForUser(?User $user, ?Team $team = null): array
    {
        if (! $user) {
            return [];
        }

        $team ??= self::activeTeam($user);

        if (! $team) {
            return [];
        }

        if (self::userBypassesWorkspaceRestrictions($user, $team)) {
            return array_keys(TeamPermissionRegistry::permissionLabels());
        }

        $member = $team->members->firstWhere('id', $user->id)
            ?? $team->members()->where('users.id', $user->id)->first();

        if (! $member) {
            return [];
        }

        $role = (string) ($member->pivot->role ?? 'member');
        $permissions = self::decodePermissions($member->pivot->permissions ?? null);
        $knownPermissions = collect($permissions)
            ->filter(fn ($permission) => TeamPermissionRegistry::hasPermission((string) $permission))
            ->map(fn ($permission) => (string) $permission)
            ->values()
            ->all();
        $containsLegacyPermissions = count($knownPermissions) !== count($permissions);
        $defaultPermissions = TeamPermissionRegistry::defaultPermissionsForRole($role, self::enabledModules($team));

        if ($knownPermissions === []) {
            return $defaultPermissions;
        }

        if ($containsLegacyPermissions) {
            return collect(array_merge($defaultPermissions, $knownPermissions))
                ->unique()
                ->values()
                ->all();
        }

        return $knownPermissions;
    }

    public static function hasPermission(?User $user, string $permission, ?Team $team = null): bool
    {
        if (! $user) {
            return false;
        }

        $team ??= self::activeTeam($user);

        if (! $team) {
            return true;
        }

        if (self::userBypassesWorkspaceRestrictions($user, $team)) {
            return true;
        }

        $module = TeamPermissionRegistry::permissionModule($permission);

        if ($module && ! self::teamHasModule($team, $module)) {
            return false;
        }

        return in_array($permission, self::permissionsForUser($user, $team), true);
    }

    public static function managedAccountIds(?User $user, ?Team $team = null): ?array
    {
        if (! $user) {
            return [];
        }

        $team ??= self::activeTeam($user);

        if (! $team) {
            return null;
        }

        if ((int) $team->owner_user_id === (int) $user->id) {
            return null;
        }

        $member = $team->members->firstWhere('id', $user->id)
            ?? $team->members()->where('users.id', $user->id)->first();

        if (! $member) {
            return [];
        }

        $managedAccountIds = $member->pivot->managed_account_ids ?? null;

        if (is_string($managedAccountIds) && trim($managedAccountIds) !== '') {
            $managedAccountIds = json_decode($managedAccountIds, true);
        }

        if (! is_array($managedAccountIds)) {
            return [];
        }

        return collect($managedAccountIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    public static function accessibleAccountsQuery(?User $user)
    {
        $ownerUserId = self::workspaceOwnerUserId($user);
        $query = SocialAccount::query()->where('created_by_user_id', $ownerUserId);
        $managedAccountIds = self::managedAccountIds($user);

        if (is_array($managedAccountIds)) {
            $query->whereKey($managedAccountIds);
        }

        return $query;
    }

    /**
     * @return array<int, string>
     */
    protected static function decodePermissions(mixed $permissions): array
    {
        if (is_array($permissions)) {
            return collect($permissions)->filter()->map(fn ($permission) => (string) $permission)->unique()->values()->all();
        }

        if (is_string($permissions) && trim($permissions) !== '') {
            $decoded = json_decode($permissions, true);

            if (is_array($decoded)) {
                return collect($decoded)->filter()->map(fn ($permission) => (string) $permission)->unique()->values()->all();
            }
        }

        return [];
    }

    protected static function userBypassesWorkspaceRestrictions(?User $user, ?Team $team): bool
    {
        return $user !== null
            && $team !== null
            && (int) $team->owner_user_id === (int) $user->id;
    }
}
