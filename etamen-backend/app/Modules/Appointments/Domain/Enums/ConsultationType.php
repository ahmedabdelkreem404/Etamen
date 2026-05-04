<?php

namespace App\Modules\Appointments\Domain\Enums;

enum ConsultationType: string
{
    case Clinic = 'clinic';
    case Online = 'online';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
