<?php

namespace App\Modules\Medications\Domain\Enums;

enum MedicationNotificationChannel: string
{
    case Local = 'local';
    case Push = 'push';
    case Email = 'email';
    case Sms = 'sms';
    case Whatsapp = 'whatsapp';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
