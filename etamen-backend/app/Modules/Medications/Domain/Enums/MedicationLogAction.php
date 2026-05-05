<?php

namespace App\Modules\Medications\Domain\Enums;

enum MedicationLogAction: string
{
    case Taken = 'taken';
    case Skipped = 'skipped';
    case Missed = 'missed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
