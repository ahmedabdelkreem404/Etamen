<?php

namespace App\Modules\Health\Domain\Enums;

enum HealthGoalType: string
{
    case WeightLoss = 'weight_loss';
    case WeightGain = 'weight_gain';
    case BloodPressureControl = 'blood_pressure_control';
    case BloodSugarControl = 'blood_sugar_control';
    case Fitness = 'fitness';
    case Sleep = 'sleep';
    case Nutrition = 'nutrition';
    case GeneralHealth = 'general_health';
    case Other = 'other';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
