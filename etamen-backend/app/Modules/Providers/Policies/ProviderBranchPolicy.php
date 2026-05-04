<?php

namespace App\Modules\Providers\Policies;

use App\Models\User;
use App\Modules\Providers\Infrastructure\Models\ProviderBranch;

class ProviderBranchPolicy
{
    public function update(User $user, ProviderBranch $providerBranch): bool
    {
        return $user->ownsProvider($providerBranch->provider);
    }
}
