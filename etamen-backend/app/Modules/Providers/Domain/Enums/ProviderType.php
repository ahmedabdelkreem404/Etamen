<?php

namespace App\Modules\Providers\Domain\Enums;

enum ProviderType: string
{
    case Doctor = 'doctor';
    case Hospital = 'hospital';
    case Clinic = 'clinic';
    case MedicalCenter = 'medical_center';
    case Pharmacy = 'pharmacy';
    case Lab = 'lab';
    case Radiology = 'radiology';
    case Gym = 'gym';
    case FitnessCoach = 'fitness_coach';
    case NutritionCoach = 'nutrition_coach';
    case Physiotherapy = 'physiotherapy';
    case HomeHealthcare = 'home_healthcare';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function publicDiscoveryValues(): array
    {
        return [
            self::Doctor->value,
            self::Pharmacy->value,
            self::Lab->value,
        ];
    }
}
