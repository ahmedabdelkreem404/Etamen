<?php

namespace App\Modules\Notifications\Infrastructure\Models;

use App\Models\User;
use App\Modules\Notifications\Domain\Enums\NotificationCategory;
use App\Modules\Notifications\Domain\Enums\NotificationChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'channel',
        'category',
        'is_enabled',
        'quiet_hours_start',
        'quiet_hours_end',
        'timezone',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'channel' => NotificationChannel::class,
            'category' => NotificationCategory::class,
            'is_enabled' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
