<?php

namespace App\Modules\Medications\Domain\Enums;

enum MedicationNotificationType: string
{
    case MedicationReminder = 'medication_reminder';
    case RefillReminder = 'refill_reminder';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
