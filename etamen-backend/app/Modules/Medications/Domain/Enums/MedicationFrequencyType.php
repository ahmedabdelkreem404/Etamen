<?php

namespace App\Modules\Medications\Domain\Enums;

enum MedicationFrequencyType: string
{
    case OnceDaily = 'once_daily';
    case TwiceDaily = 'twice_daily';
    case ThreeTimesDaily = 'three_times_daily';
    case CustomTimes = 'custom_times';
    case EveryXHours = 'every_x_hours';
    case SpecificDays = 'specific_days';
    case AsNeeded = 'as_needed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
