<?php

namespace App\Modules\Health\Domain\Enums;

enum VitalType: string
{
    case BloodPressure = 'blood_pressure';
    case BloodSugar = 'blood_sugar';
    case HeartRate = 'heart_rate';
    case OxygenSaturation = 'oxygen_saturation';
    case Temperature = 'temperature';
    case Weight = 'weight';
    case Sleep = 'sleep';
    case Mood = 'mood';
    case Symptom = 'symptom';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
