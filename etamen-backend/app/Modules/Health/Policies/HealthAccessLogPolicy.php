<?php

namespace App\Modules\Health\Policies;

use App\Models\User;
use App\Modules\Health\Infrastructure\Models\HealthAccessLog;

class HealthAccessLogPolicy
{
    public function view(User $user, HealthAccessLog $log): bool
    {
        return $user->isPlatformAdmin();
    }
}
