<?php

namespace Modules\AppAiPublishing\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AdminUser\Models\User;

class AiPublishingRun extends Model
{
    protected $table = 'ai_publishing_runs';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'account_ids' => 'array',
            'label_ids' => 'array',
            'prompt_ids' => 'array',
            'schedule_config' => 'array',
            'generation_config' => 'array',
            'stats' => 'array',
            'last_processed_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('owner_user_id', $userId);
    }
}
