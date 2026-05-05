<?php

namespace App\Modules\Wallets\Application\Services;

use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Wallets\Domain\Enums\WalletOwnerType;
use App\Modules\Wallets\Domain\Enums\WalletStatus;
use App\Modules\Wallets\Infrastructure\Models\Wallet;
use Illuminate\Validation\ValidationException;

class WalletService
{
    public function walletForProvider(Provider $provider, string $currency = 'EGP'): Wallet
    {
        if ($provider->type !== ProviderType::Doctor) {
            throw ValidationException::withMessages([
                'provider' => ['Only doctor provider wallets are active in this sprint.'],
            ]);
        }

        return Wallet::query()->firstOrCreate(
            [
                'owner_type' => WalletOwnerType::Doctor,
                'owner_id' => $provider->id,
                'currency' => $currency,
            ],
            ['status' => WalletStatus::Active],
        );
    }
}
