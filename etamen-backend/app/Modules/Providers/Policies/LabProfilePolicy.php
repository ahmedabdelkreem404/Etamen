<?php

namespace App\Modules\Providers\Policies;

use App\Models\User;
use App\Modules\Providers\Infrastructure\Models\LabProfile;

class LabProfilePolicy
{
    public function update(User $user, LabProfile $labProfile): bool
    {
        return $user->ownsProvider($labProfile->provider);
    }
}
