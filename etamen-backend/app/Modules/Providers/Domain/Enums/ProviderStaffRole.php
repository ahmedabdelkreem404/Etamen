<?php

namespace App\Modules\Providers\Domain\Enums;

enum ProviderStaffRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Staff = 'staff';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
