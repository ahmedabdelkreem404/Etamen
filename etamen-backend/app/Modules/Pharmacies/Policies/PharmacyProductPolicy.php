<?php

namespace App\Modules\Pharmacies\Policies;

use App\Models\User;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyProduct;
use App\Modules\Providers\Infrastructure\Models\Provider;

class PharmacyProductPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }

    public function view(User $user, PharmacyProduct $product): bool
    {
        return $this->ownsProductProvider($user, $product);
    }

    public function update(User $user, PharmacyProduct $product): bool
    {
        return $this->ownsProductProvider($user, $product);
    }

    public function delete(User $user, PharmacyProduct $product): bool
    {
        return $this->ownsProductProvider($user, $product);
    }

    private function ownsProductProvider(User $user, PharmacyProduct $product): bool
    {
        $provider = Provider::query()->find($product->provider_id);

        return $provider && $user->ownsProvider($provider);
    }
}
