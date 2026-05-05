<?php

namespace App\Modules\Pharmacies\Policies;

use App\Models\User;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyOrder;
use App\Modules\Providers\Infrastructure\Models\Provider;

class PharmacyOrderPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }

    public function view(User $user, PharmacyOrder $order): bool
    {
        return (int) $order->patient_user_id === (int) $user->id
            || $this->ownsOrderProvider($user, $order);
    }

    public function pay(User $user, PharmacyOrder $order): bool
    {
        return (int) $order->patient_user_id === (int) $user->id;
    }

    public function providerView(User $user, PharmacyOrder $order): bool
    {
        return $this->ownsOrderProvider($user, $order);
    }

    public function providerManage(User $user, PharmacyOrder $order): bool
    {
        return $this->ownsOrderProvider($user, $order);
    }

    private function ownsOrderProvider(User $user, PharmacyOrder $order): bool
    {
        $provider = Provider::query()->find($order->pharmacy_provider_id);

        return $provider && $user->ownsProvider($provider);
    }
}
