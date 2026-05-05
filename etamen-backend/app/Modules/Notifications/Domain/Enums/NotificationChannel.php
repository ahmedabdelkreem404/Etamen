<?php

namespace App\Modules\Notifications\Domain\Enums;

enum NotificationChannel: string
{
    case InApp = 'in_app';
    case Push = 'push';
    case Email = 'email';
    case Sms = 'sms';
    case WhatsApp = 'whatsapp';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
