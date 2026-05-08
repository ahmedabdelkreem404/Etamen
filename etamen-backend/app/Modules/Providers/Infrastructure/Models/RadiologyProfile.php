<?php

namespace App\Modules\Providers\Infrastructure\Models;

use App\Modules\Providers\Domain\Enums\ProviderType;

class RadiologyProfile extends ProviderTypedProfile
{
    protected $fillable = [
        'provider_id',
        'license_number',
        'home_service_enabled',
        'report_delivery_enabled',
        'dicom_supported',
        'description_ar',
        'description_en',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'home_service_enabled' => 'boolean',
            'report_delivery_enabled' => 'boolean',
            'dicom_supported' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function requiredProviderType(): ProviderType
    {
        return ProviderType::Radiology;
    }
}
