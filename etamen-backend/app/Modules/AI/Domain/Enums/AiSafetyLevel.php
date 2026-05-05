<?php

namespace App\Modules\AI\Domain\Enums;

enum AiSafetyLevel: string
{
    case Standard = 'standard';
    case Strict = 'strict';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
