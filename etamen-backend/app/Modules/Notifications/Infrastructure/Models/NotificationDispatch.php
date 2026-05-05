<?php

namespace App\Modules\Notifications\Infrastructure\Models;

use App\Models\User;
use App\Modules\Notifications\Domain\Enums\NotificationCategory;
use App\Modules\Notifications\Domain\Enums\NotificationChannel;
use App\Modules\Notifications\Domain\Enums\NotificationDispatchStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDispatch extends Model
{
    protected $fillable = [
        'notification_id',
        'user_id',
        'channel',
        'provider',
        'category',
        'type',
        'recipient',
        'title',
        'body',
        'payload',
        'status',
        'idempotency_key',
        'scheduled_for',
        'attempted_at',
        'sent_at',
        'failure_reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'channel' => NotificationChannel::class,
            'category' => NotificationCategory::class,
            'payload' => 'array',
            'status' => NotificationDispatchStatus::class,
            'scheduled_for' => 'datetime',
            'attempted_at' => 'datetime',
            'sent_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
