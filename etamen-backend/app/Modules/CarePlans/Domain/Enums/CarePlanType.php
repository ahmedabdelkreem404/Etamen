<?php

namespace App\Modules\CarePlans\Domain\Enums;

enum CarePlanType: string
{
    case Nutrition = 'nutrition';
    case GeneralCare = 'general_care';
    case WeightManagement = 'weight_management';
    case DiabetesFollowup = 'diabetes_followup';
    case BloodPressureFollowup = 'blood_pressure_followup';
    case FitnessFollowup = 'fitness_followup';
    case RecoveryFollowup = 'recovery_followup';
    case Other = 'other';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
