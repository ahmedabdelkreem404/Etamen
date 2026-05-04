<?php

namespace App\Modules\Providers\Policies;

use App\Models\User;
use App\Modules\Providers\Infrastructure\Models\DoctorProfile;

class DoctorProfilePolicy
{
    public function update(User $user, DoctorProfile $doctorProfile): bool
    {
        return $user->ownsProvider($doctorProfile->provider);
    }
}
