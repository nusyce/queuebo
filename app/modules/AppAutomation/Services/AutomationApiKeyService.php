<?php

namespace Modules\AppAutomation\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\AdminUser\Models\User;
use Modules\AppAutomation\Models\AutomationApiKey;
use Modules\AppAutomation\Support\AutomationAccess;

class AutomationApiKeyService
{
    public function __construct(
        protected AutomationAccess $access,
    ) {}

    public function create(User $user, string $name, array $permissions = ['posts:write', 'accounts:read']): array
    {
        $plain = 'spauto_'.Str::random(40);
        $workspaceOwnerUserId = $this->access->workspaceOwnerUserId($user);

        $key = AutomationApiKey::query()->create([
            'user_id' => $workspaceOwnerUserId,
            'team_id' => $this->access->teamId($user),
            'name' => trim($name),
            'key_hash' => hash('sha256', $plain),
            'key_prefix' => substr($plain, 0, 12),
            'permissions' => array_values(array_unique(array_filter(array_map(
                static fn ($permission) => trim((string) $permission),
                $permissions
            )))),
            'last_used_at' => null,
            'revoked_at' => null,
        ]);

        return [$key, $plain];
    }

    public function resolveFromRequest(Request $request): ?AutomationApiKey
    {
        $candidate = trim((string) $request->bearerToken());

        if ($candidate === '') {
            $candidate = trim((string) $request->header('X-Stackposts-Key', ''));
        }

        if ($candidate === '') {
            return null;
        }

        $hash = hash('sha256', $candidate);

        return AutomationApiKey::query()
            ->where('key_hash', $hash)
            ->whereNull('revoked_at')
            ->first();
    }

    public function touch(AutomationApiKey $key): void
    {
        $key->forceFill(['last_used_at' => now()])->save();
    }
}
