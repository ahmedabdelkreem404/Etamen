<?php

namespace App\Modules\Providers\Infrastructure\Models;

use App\Modules\Providers\Domain\Enums\ProviderType;

class HomeHealthcareProfile extends ProviderTypedProfile
{
    protected $fillable = [
        'provider_id',
        'nursing_enabled',
        'injections_enabled',
        'wound_care_enabled',
        'elderly_care_enabled',
        'physiotherapy_home_enabled',
        'service_radius_km',
        'description_ar',
        'description_en',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'nursing_enabled' => 'boolean',
            'injections_enabled' => 'boolean',
            'wound_care_enabled' => 'boolean',
            'elderly_care_enabled' => 'boolean',
            'physiotherapy_home_enabled' => 'boolean',
            'service_radius_km' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected static function requiredProviderType(): ProviderType
    {
        return ProviderType::HomeHealthcare;
    }
}
