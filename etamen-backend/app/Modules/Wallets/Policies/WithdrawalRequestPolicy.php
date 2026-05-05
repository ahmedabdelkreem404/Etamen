<?php

namespace App\Modules\Wallets\Policies;

use App\Models\User;
use App\Modules\Wallets\Infrastructure\Models\WithdrawalRequest;

class WithdrawalRequestPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }

    public function view(User $user, WithdrawalRequest $withdrawalRequest): bool
    {
        return $user->can('view', $withdrawalRequest->wallet);
    }
}
