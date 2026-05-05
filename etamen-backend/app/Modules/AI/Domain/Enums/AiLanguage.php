<?php

namespace App\Modules\AI\Domain\Enums;

enum AiLanguage: string
{
    case Arabic = 'ar';
    case English = 'en';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
