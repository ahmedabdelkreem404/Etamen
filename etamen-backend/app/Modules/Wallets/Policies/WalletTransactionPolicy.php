<?php

namespace App\Modules\Wallets\Policies;

use App\Models\User;
use App\Modules\Wallets\Infrastructure\Models\WalletTransaction;

class WalletTransactionPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }

    public function view(User $user, WalletTransaction $walletTransaction): bool
    {
        return $user->can('view', $walletTransaction->wallet);
    }
}
