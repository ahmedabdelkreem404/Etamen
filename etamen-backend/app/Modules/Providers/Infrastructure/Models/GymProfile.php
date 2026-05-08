<?php

namespace App\Modules\Providers\Infrastructure\Models;

use App\Modules\Providers\Domain\Enums\ProviderType;

class GymProfile extends ProviderTypedProfile
{
    protected $fillable = [
        'provider_id',
        'men_allowed',
        'women_allowed',
        'ladies_only_hours',
        'has_classes',
        'has_personal_training',
        'description_ar',
        'description_en',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'men_allowed' => 'boolean',
            'women_allowed' => 'boolean',
            'ladies_only_hours' => 'boolean',
            'has_classes' => 'boolean',
            'has_personal_training' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function requiredProviderType(): ProviderType
    {
        return ProviderType::Gym;
    }
}
