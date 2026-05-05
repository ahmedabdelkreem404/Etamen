<?php

namespace App\Modules\Wallets\Policies;

use App\Models\User;

class CommissionRulePolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }
}
