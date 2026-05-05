<?php

namespace App\Modules\Medications\Domain\Enums;

enum MedicationRefillEventType: string
{
    case RefillDue = 'refill_due';
    case RefillDone = 'refill_done';
    case RefillSkipped = 'refill_skipped';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
