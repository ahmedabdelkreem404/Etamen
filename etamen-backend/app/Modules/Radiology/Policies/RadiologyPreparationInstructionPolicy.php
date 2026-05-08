<?php

namespace App\Modules\Radiology\Policies;

use App\Models\User;

class RadiologyPreparationInstructionPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }
}
