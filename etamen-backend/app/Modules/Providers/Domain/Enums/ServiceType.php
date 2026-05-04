<?php

namespace App\Modules\Providers\Domain\Enums;

enum ServiceType: string
{
    case Appointment = 'appointment';
    case PharmacyOrder = 'pharmacy_order';
    case LabOrder = 'lab_order';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
