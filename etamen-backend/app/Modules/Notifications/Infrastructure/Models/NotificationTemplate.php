<?php

namespace App\Modules\Notifications\Infrastructure\Models;

use App\Modules\Notifications\Domain\Enums\NotificationCategory;
use App\Modules\Notifications\Domain\Enums\NotificationChannel;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'key',
        'category',
        'title_ar',
        'title_en',
        'body_ar',
        'body_en',
        'channel',
        'is_active',
        'variables',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'category' => NotificationCategory::class,
            'channel' => NotificationChannel::class,
            'is_active' => 'boolean',
            'variables' => 'array',
            'metadata' => 'array',
        ];
    }
}
