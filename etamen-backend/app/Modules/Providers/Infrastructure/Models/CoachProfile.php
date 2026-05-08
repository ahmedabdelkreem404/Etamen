<?php

namespace App\Modules\Providers\Infrastructure\Models;

use App\Modules\Providers\Domain\Enums\CoachType;
use App\Modules\Providers\Domain\Enums\ProviderType;
use Illuminate\Validation\ValidationException;

class CoachProfile extends ProviderTypedProfile
{
    protected $fillable = [
        'provider_id',
        'coach_type',
        'experience_years',
        'session_price',
        'monthly_followup_price',
        'online_coaching_enabled',
        'gym_visit_enabled',
        'home_training_enabled',
        'certifications_summary',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'coach_type' => CoachType::class,
            'experience_years' => 'integer',
            'session_price' => 'decimal:2',
            'monthly_followup_price' => 'decimal:2',
            'online_coaching_enabled' => 'boolean',
            'gym_visit_enabled' => 'boolean',
            'home_training_enabled' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function requiredProviderType(): ProviderType
    {
        return ProviderType::FitnessCoach;
    }

    protected static function booted(): void
    {
        static::saving(function (CoachProfile $profile): void {
            $provider = Provider::query()->find($profile->provider_id);

            if (! $provider || ! in_array($provider->type, [ProviderType::FitnessCoach, ProviderType::NutritionCoach], true)) {
                throw ValidationException::withMessages([
                    'provider_id' => ['Provider type must be fitness_coach or nutrition_coach for coach profiles.'],
                ]);
            }
        });
    }
}
