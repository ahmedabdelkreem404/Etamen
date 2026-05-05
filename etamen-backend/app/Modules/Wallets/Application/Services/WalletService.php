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
        if (! in_array($provider->type, [ProviderType::Doctor, ProviderType::Pharmacy], true)) {
            throw ValidationException::withMessages([
                'provider' => ['Only doctor and pharmacy provider wallets are active in this sprint.'],
            ]);
        }

        return Wallet::query()->firstOrCreate(
            [
                'owner_type' => $this->ownerTypeFor($provider->type),
                'owner_id' => $provider->id,
                'currency' => $currency,
            ],
            ['status' => WalletStatus::Active],
        );
    }

    private function ownerTypeFor(ProviderType $providerType): WalletOwnerType
    {
        return match ($providerType) {
            ProviderType::Doctor => WalletOwnerType::Doctor,
            ProviderType::Pharmacy => WalletOwnerType::Pharmacy,
            ProviderType::Lab => WalletOwnerType::Lab,
        };
    }
}
