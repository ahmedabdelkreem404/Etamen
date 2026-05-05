<?php

namespace App\Modules\Medications\Domain\Enums;

enum MedicationReminderSource: string
{
    case PatientEntered = 'patient_entered';
    case Imported = 'imported';
    case ProviderEntered = 'provider_entered';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
