<?php

namespace App\Modules\Wallets\Domain\Enums;

enum WalletOwnerType: string
{
    case Doctor = 'doctor';
    case Pharmacy = 'pharmacy';
    case Lab = 'lab';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
