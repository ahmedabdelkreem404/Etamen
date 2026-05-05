<?php

namespace App\Modules\CarePlans\Domain\Enums;

enum CarePlanMood: string
{
    case VeryBad = 'very_bad';
    case Bad = 'bad';
    case Neutral = 'neutral';
    case Good = 'good';
    case VeryGood = 'very_good';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
