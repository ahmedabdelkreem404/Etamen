<?php

namespace App\Modules\Providers\Policies;

use App\Models\User;
use App\Modules\Providers\Infrastructure\Models\PharmacyProfile;

class PharmacyProfilePolicy
{
    public function update(User $user, PharmacyProfile $pharmacyProfile): bool
    {
        return $user->ownsProvider($pharmacyProfile->provider);
    }
}
