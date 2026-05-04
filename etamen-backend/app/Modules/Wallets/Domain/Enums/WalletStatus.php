<?php

namespace App\Modules\Wallets\Domain\Enums;

enum WalletStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Closed = 'closed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
