<?php

namespace App\Modules\Wallets\Infrastructure\Models;

use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Domain\Enums\ServiceType;
use Illuminate\Database\Eloquent\Model;

class CommissionRule extends Model
{
    protected $fillable = [
        'provider_type',
        'service_type',
        'percentage',
        'fixed_amount',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'provider_type' => ProviderType::class,
            'service_type' => ServiceType::class,
            'percentage' => 'decimal:2',
            'fixed_amount' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
