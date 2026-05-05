<?php

namespace App\Modules\Notifications\Domain\Enums;

enum NotificationDeviceType: string
{
    case Android = 'android';
    case Ios = 'ios';
    case Web = 'web';
    case Unknown = 'unknown';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
