<?php

namespace App\Modules\Radiology\Policies;

use App\Models\User;
use App\Modules\Radiology\Infrastructure\Models\RadiologyScan;

class RadiologyScanPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }

    public function view(User $user, RadiologyScan $scan): bool
    {
        return $user->ownsProvider($scan->provider);
    }

    public function update(User $user, RadiologyScan $scan): bool
    {
        return $user->ownsProvider($scan->provider);
    }

    public function delete(User $user, RadiologyScan $scan): bool
    {
        return $user->ownsProvider($scan->provider);
    }
}
