<?php

namespace App\Modules\Labs\Domain\Enums;

enum LabResultStatus: string
{
    case Uploaded = 'uploaded';
    case VisibleToPatient = 'visible_to_patient';
    case Hidden = 'hidden';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
