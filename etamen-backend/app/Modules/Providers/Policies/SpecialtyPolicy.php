<?php

namespace App\Modules\Providers\Policies;

use App\Models\User;
use App\Modules\Providers\Infrastructure\Models\Specialty;

class SpecialtyPolicy
{
    public function create(User $user): bool
    {
        return $user->isPlatformAdmin();
    }

    public function update(User $user, Specialty $specialty): bool
    {
        return $user->isPlatformAdmin();
    }

    public function delete(User $user, Specialty $specialty): bool
    {
        return $user->isPlatformAdmin();
    }
}
