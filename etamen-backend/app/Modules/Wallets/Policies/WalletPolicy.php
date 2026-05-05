<?php

namespace App\Modules\Wallets\Policies;

use App\Models\User;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Wallets\Infrastructure\Models\Wallet;

class WalletPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }

    public function view(User $user, Wallet $wallet): bool
    {
        $provider = Provider::query()->find($wallet->owner_id);

        return $provider && $user->ownsProvider($provider);
    }
}
