<?php

namespace App\Modules\CarePlans\Domain\Enums;

enum CarePlanSource: string
{
    case PatientCreated = 'patient_created';
    case ProviderAssigned = 'provider_assigned';
    case AdminCreated = 'admin_created';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
