<?php

namespace App\Modules\Wallets\Infrastructure\Models;

use App\Modules\Providers\Domain\Enums\ProviderType;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'provider_type',
        'name_ar',
        'name_en',
        'duration_days',
        'price',
        'currency',
        'benefits',
        'visibility_priority',
        'feature_limits',
        'has_free_trial',
        'trial_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'provider_type' => ProviderType::class,
            'duration_days' => 'integer',
            'price' => 'decimal:2',
            'benefits' => 'array',
            'visibility_priority' => 'integer',
            'feature_limits' => 'array',
            'has_free_trial' => 'boolean',
            'trial_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
