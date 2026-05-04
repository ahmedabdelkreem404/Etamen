<?php

namespace App\Modules\Providers\Domain\Enums;

enum ProviderType: string
{
    case Doctor = 'doctor';
    case Pharmacy = 'pharmacy';
    case Lab = 'lab';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
