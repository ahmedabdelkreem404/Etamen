<?php

namespace App\Modules\Labs\Policies;

use App\Models\User;
use App\Modules\Labs\Infrastructure\Models\LabTest;
use App\Modules\Providers\Infrastructure\Models\Provider;

class LabTestPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }

    public function view(User $user, LabTest $test): bool
    {
        return $this->ownsProvider($user, $test->provider_id);
    }

    public function update(User $user, LabTest $test): bool
    {
        return $this->ownsProvider($user, $test->provider_id);
    }

    public function delete(User $user, LabTest $test): bool
    {
        return $this->ownsProvider($user, $test->provider_id);
    }

    private function ownsProvider(User $user, int $providerId): bool
    {
        $provider = Provider::query()->find($providerId);

        return $provider && $user->ownsProvider($provider);
    }
}
