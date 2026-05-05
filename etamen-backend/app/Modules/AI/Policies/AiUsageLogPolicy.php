<?php

namespace App\Modules\AI\Policies;

use App\Models\User;
use App\Modules\AI\Infrastructure\Models\AiUsageLog;

class AiUsageLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin']);
    }

    public function view(User $user, AiUsageLog $log): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin']);
    }
}
