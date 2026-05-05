<?php

namespace App\Modules\Payments\Policies;

use App\Models\User;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Providers\Infrastructure\Models\Provider;

class PaymentPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }

    public function view(User $user, Payment $payment): bool
    {
        return (int) $payment->user_id === (int) $user->id
            || $this->ownsPaymentProvider($user, $payment);
    }

    public function uploadProof(User $user, Payment $payment): bool
    {
        return (int) $payment->user_id === (int) $user->id;
    }

    private function ownsPaymentProvider(User $user, Payment $payment): bool
    {
        if (! $payment->provider_id) {
            return false;
        }

        $provider = Provider::query()->find($payment->provider_id);

        return $provider && $user->ownsProvider($provider);
    }
}
