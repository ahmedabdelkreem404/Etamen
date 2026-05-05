<?php

namespace App\Modules\CarePlans\Domain\Enums;

enum CarePlanVisibility: string
{
    case PatientOnly = 'patient_only';
    case ProviderAssigned = 'provider_assigned';
    case AdminManaged = 'admin_managed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
