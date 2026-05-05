<?php

namespace App\Modules\AI\Domain\Enums;

enum AiConversationStatus: string
{
    case Active = 'active';
    case Archived = 'archived';
    case Blocked = 'blocked';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
