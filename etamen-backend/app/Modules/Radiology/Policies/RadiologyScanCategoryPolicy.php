<?php

namespace App\Modules\Radiology\Policies;

use App\Models\User;

class RadiologyScanCategoryPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }
}
