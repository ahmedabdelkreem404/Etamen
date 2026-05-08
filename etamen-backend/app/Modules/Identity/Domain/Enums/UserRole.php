<?php

namespace App\Modules\Identity\Domain\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Patient = 'patient';
    case ProviderAdmin = 'provider_admin';
    case Doctor = 'doctor';
    case PharmacyAdmin = 'pharmacy_admin';
    case LabAdmin = 'lab_admin';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
