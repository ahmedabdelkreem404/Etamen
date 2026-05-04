<?php

namespace App\Modules\Settings\Infrastructure\Models;

use App\Modules\AuditLogs\Application\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'is_encrypted',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::updated(function (Setting $setting): void {
            app(AuditLogService::class)->log(
                'setting.updated',
                $setting,
                before: $setting->getOriginal(),
                after: $setting->getAttributes(),
            );
        });
    }
}
