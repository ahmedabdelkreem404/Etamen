<?php

namespace App\Modules\Notifications\Policies;

use App\Models\User;
use App\Modules\Notifications\Infrastructure\Models\NotificationDispatch;

class NotificationDispatchPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasAnyRole(['admin', 'super_admin']) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, NotificationDispatch $dispatch): bool
    {
        return false;
    }
}
