<?php

namespace App\Modules\AI\Domain\Enums;

enum AiProvider: string
{
    case DeepSeek = 'deepseek';
    case Gemini = 'gemini';
    case Fake = 'fake';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
