<?php

namespace App\Modules\AI\Domain\Enums;

enum AiMessageRole: string
{
    case User = 'user';
    case Assistant = 'assistant';
    case System = 'system';
    case Safety = 'safety';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
