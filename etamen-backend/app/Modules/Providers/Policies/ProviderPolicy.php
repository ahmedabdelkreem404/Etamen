<?php

namespace App\Modules\Providers\Policies;

use App\Models\User;
use App\Modules\Providers\Infrastructure\Models\Provider;

class ProviderPolicy
{
    public function update(User $user, Provider $provider): bool
    {
        return $user->ownsProvider($provider);
    }

    public function approve(User $user, Provider $provider): bool
    {
        return $user->isPlatformAdmin();
    }

    public function reject(User $user, Provider $provider): bool
    {
        return $user->isPlatformAdmin();
    }

    public function suspend(User $user, Provider $provider): bool
    {
        return $user->isPlatformAdmin();
    }

    public function reactivate(User $user, Provider $provider): bool
    {
        return $user->isPlatformAdmin();
    }
}
