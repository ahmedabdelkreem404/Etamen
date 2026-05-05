<?php

namespace App\Modules\AI\Policies;

use App\Models\User;
use App\Modules\AI\Infrastructure\Models\AiSafetyEvent;

class AiSafetyEventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin']);
    }

    public function view(User $user, AiSafetyEvent $event): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin']);
    }
}
