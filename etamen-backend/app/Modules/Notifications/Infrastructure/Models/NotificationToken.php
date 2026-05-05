<?php

namespace App\Modules\Notifications\Infrastructure\Models;

use App\Models\User;
use App\Modules\Notifications\Domain\Enums\NotificationDeviceType;
use App\Modules\Notifications\Domain\Enums\NotificationTokenProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationToken extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'token_hash',
        'provider',
        'device_type',
        'device_name',
        'app_version',
        'locale',
        'timezone',
        'is_active',
        'last_seen_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'provider' => NotificationTokenProvider::class,
            'device_type' => NotificationDeviceType::class,
            'is_active' => 'boolean',
            'last_seen_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
