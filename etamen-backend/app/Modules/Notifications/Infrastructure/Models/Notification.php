<?php

namespace App\Modules\Notifications\Infrastructure\Models;

use App\Models\User;
use App\Modules\Notifications\Domain\Enums\NotificationCategory;
use App\Modules\Notifications\Domain\Enums\NotificationPriority;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'category',
        'type',
        'title',
        'body',
        'data',
        'priority',
        'read_at',
        'action_url',
    ];

    protected function casts(): array
    {
        return [
            'category' => NotificationCategory::class,
            'data' => 'array',
            'priority' => NotificationPriority::class,
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dispatches(): HasMany
    {
        return $this->hasMany(NotificationDispatch::class);
    }
}
