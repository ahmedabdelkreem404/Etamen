<?php

namespace App\Modules\Notifications\Domain\Enums;

enum NotificationTokenProvider: string
{
    case Fcm = 'fcm';
    case Apns = 'apns';
    case WebPush = 'web_push';
    case Local = 'local';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
