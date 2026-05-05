<?php

namespace App\Modules\Payments\Policies;

use App\Models\User;
use App\Modules\Payments\Infrastructure\Models\PaymentProof;

class PaymentProofPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }

    public function view(User $user, PaymentProof $paymentProof): bool
    {
        return (int) $paymentProof->payment->user_id === (int) $user->id;
    }
}
