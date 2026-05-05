<?php

namespace App\Modules\AI\Policies;

use App\Models\User;
use App\Modules\AI\Infrastructure\Models\AiProviderConfig;

class AiProviderConfigPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin']);
    }

    public function view(User $user, AiProviderConfig $config): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin']);
    }

    public function update(User $user, AiProviderConfig $config): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin']);
    }
}
