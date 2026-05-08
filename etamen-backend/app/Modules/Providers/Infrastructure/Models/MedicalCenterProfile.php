<?php

namespace App\Modules\Providers\Infrastructure\Models;

use App\Modules\Providers\Domain\Enums\ProviderType;

class MedicalCenterProfile extends ProviderTypedProfile
{
    protected $fillable = [
        'provider_id',
        'center_type',
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
        return ProviderType::MedicalCenter;
    }
}
