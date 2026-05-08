<?php

namespace App\Modules\Providers\Infrastructure\Models;

use App\Modules\Providers\Domain\Enums\ProviderType;

class HospitalProfile extends ProviderTypedProfile
{
    protected $fillable = [
        'provider_id',
        'license_number',
        'description_ar',
        'description_en',
        'emergency_available',
        'has_inpatient',
        'has_outpatient',
        'has_icu',
        'has_ambulance',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'emergency_available' => 'boolean',
            'has_inpatient' => 'boolean',
            'has_outpatient' => 'boolean',
            'has_icu' => 'boolean',
            'has_ambulance' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function requiredProviderType(): ProviderType
    {
        return ProviderType::Hospital;
    }
}
