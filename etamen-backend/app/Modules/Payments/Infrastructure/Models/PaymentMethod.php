<?php

namespace App\Modules\Payments\Infrastructure\Models;

use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Payments\Domain\Enums\PaymentMethodType;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'type',
        'name_ar',
        'name_en',
        'is_active',
        'config',
        'instructions_ar',
        'instructions_en',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => PaymentMethodType::class,
            'is_active' => 'boolean',
            'config' => 'encrypted:array',
        ];
    }

    protected static function booted(): void
    {
        static::updated(function (PaymentMethod $paymentMethod): void {
            app(AuditLogService::class)->log(
                'payment_method.updated',
                $paymentMethod,
                before: $paymentMethod->getOriginal(),
                after: $paymentMethod->getAttributes(),
            );
        });
    }
}
