<?php

namespace App\Modules\Wallets\Domain\Enums;

enum WalletTransactionStatus: string
{
    case Pending = 'pending';
    case Posted = 'posted';
    case Reversed = 'reversed';
    case Cancelled = 'cancelled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
