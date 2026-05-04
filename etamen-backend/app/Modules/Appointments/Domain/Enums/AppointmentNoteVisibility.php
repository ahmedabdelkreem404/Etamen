<?php

namespace App\Modules\Appointments\Domain\Enums;

enum AppointmentNoteVisibility: string
{
    case Patient = 'patient';
    case Doctor = 'doctor';
    case Admin = 'admin';
    case Internal = 'internal';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
