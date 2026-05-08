<?php

namespace App\Modules\Providers\Infrastructure\Models;

use App\Modules\Providers\Domain\Enums\ProviderType;

class ClinicProfile extends ProviderTypedProfile
{
    protected $fillable = [
        'provider_id',
        'clinic_type',
        'description_ar',
        'description_en',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function requiredProviderType(): ProviderType
    {
        return ProviderType::Clinic;
    }
}
