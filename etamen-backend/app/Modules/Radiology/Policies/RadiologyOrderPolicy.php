<?php

namespace App\Modules\Radiology\Policies;

use App\Models\User;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Radiology\Infrastructure\Models\RadiologyOrder;

class RadiologyOrderPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }

    public function view(User $user, RadiologyOrder $order): bool
    {
        return (int) $order->patient_user_id === (int) $user->id
            || $this->ownsRadiologyProvider($user, $order);
    }

    public function cancel(User $user, RadiologyOrder $order): bool
    {
        return (int) $order->patient_user_id === (int) $user->id;
    }

    public function providerView(User $user, RadiologyOrder $order): bool
    {
        return $this->ownsRadiologyProvider($user, $order);
    }

    public function providerManage(User $user, RadiologyOrder $order): bool
    {
        return $this->ownsRadiologyProvider($user, $order);
    }

    public function uploadResult(User $user, RadiologyOrder $order): bool
    {
        return $this->ownsRadiologyProvider($user, $order);
    }

    private function ownsRadiologyProvider(User $user, RadiologyOrder $order): bool
    {
        $provider = Provider::query()->find($order->provider_id);

        return $provider
            && $provider->type === ProviderType::Radiology
            && $user->ownsProvider($provider);
    }
}
