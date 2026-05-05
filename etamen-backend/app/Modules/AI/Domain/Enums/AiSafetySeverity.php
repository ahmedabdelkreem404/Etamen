<?php

namespace App\Modules\AI\Domain\Enums;

enum AiSafetySeverity: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Critical = 'critical';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
