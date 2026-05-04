<?php

namespace App\Modules\Wallets\Domain\Enums;

enum WalletTransactionType: string
{
    case Credit = 'credit';
    case Debit = 'debit';
    case Hold = 'hold';
    case Release = 'release';
    case Commission = 'commission';
    case Reversal = 'reversal';
    case Withdrawal = 'withdrawal';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
