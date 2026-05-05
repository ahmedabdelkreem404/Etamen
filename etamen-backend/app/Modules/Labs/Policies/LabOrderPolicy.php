<?php

namespace App\Modules\Labs\Policies;

use App\Models\User;
use App\Modules\Labs\Infrastructure\Models\LabOrder;
use App\Modules\Providers\Infrastructure\Models\Provider;

class LabOrderPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }

    public function view(User $user, LabOrder $order): bool
    {
        return (int) $order->patient_user_id === (int) $user->id
            || $this->ownsOrderProvider($user, $order);
    }

    public function pay(User $user, LabOrder $order): bool
    {
        return (int) $order->patient_user_id === (int) $user->id;
    }

    public function providerView(User $user, LabOrder $order): bool
    {
        return $this->ownsOrderProvider($user, $order);
    }

    public function providerManage(User $user, LabOrder $order): bool
    {
        return $this->ownsOrderProvider($user, $order);
    }

    public function uploadResult(User $user, LabOrder $order): bool
    {
        return $this->ownsOrderProvider($user, $order);
    }

    private function ownsOrderProvider(User $user, LabOrder $order): bool
    {
        $provider = Provider::query()->find($order->lab_provider_id);

        return $provider && $user->ownsProvider($provider);
    }
}
