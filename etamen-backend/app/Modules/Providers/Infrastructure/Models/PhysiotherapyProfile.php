<?php

namespace App\Modules\Providers\Infrastructure\Models;

use App\Modules\Providers\Domain\Enums\ProviderType;

class PhysiotherapyProfile extends ProviderTypedProfile
{
    protected $fillable = [
        'provider_id',
        'home_visit_enabled',
        'center_visit_enabled',
        'session_price',
        'description_ar',
        'description_en',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'home_visit_enabled' => 'boolean',
            'center_visit_enabled' => 'boolean',
            'session_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected static function requiredProviderType(): ProviderType
    {
        return ProviderType::Physiotherapy;
    }
}
