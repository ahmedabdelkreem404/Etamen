<?php

namespace App\Modules\Wallets\Application\Services;

use App\Modules\Wallets\Domain\Enums\WalletTransactionStatus;
use App\Modules\Wallets\Domain\Enums\WalletTransactionType;
use App\Modules\Wallets\Infrastructure\Models\Wallet;

class WalletBalanceService
{
    public function summary(Wallet $wallet): array
    {
        $posted = $wallet->transactions()->where('status', WalletTransactionStatus::Posted);

        $hold = (clone $posted)->where('type', WalletTransactionType::Hold)->sum('net_amount');
        $release = (clone $posted)->where('type', WalletTransactionType::Release)->sum('net_amount');
        $withdrawal = (clone $posted)->where('type', WalletTransactionType::Withdrawal)->sum('net_amount');
        $reversal = (clone $posted)->where('type', WalletTransactionType::Reversal)->sum('net_amount');
        $commission = (clone $posted)->where('type', WalletTransactionType::Commission)->sum('commission_amount');

        return [
            'pending_balance' => round(max((float) $hold - (float) $release - (float) $reversal, 0), 2),
            'available_balance' => round(max((float) $release - (float) $withdrawal - (float) $reversal, 0), 2),
            'withdrawn_balance' => round((float) $withdrawal, 2),
            'total_commission' => round((float) $commission, 2),
            'currency' => $wallet->currency,
        ];
    }
}
