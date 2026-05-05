<?php

namespace App\Modules\Health\Domain\Enums;

enum VitalFlag: string
{
    case VeryLow = 'very_low';
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
    case VeryHigh = 'very_high';
    case Unknown = 'unknown';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
