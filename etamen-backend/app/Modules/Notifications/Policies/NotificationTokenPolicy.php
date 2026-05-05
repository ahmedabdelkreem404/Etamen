<?php

namespace App\Modules\Notifications\Policies;

use App\Models\User;
use App\Modules\Notifications\Infrastructure\Models\NotificationToken;

class NotificationTokenPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasAnyRole(['admin', 'super_admin']) ? true : null;
    }

    public function delete(User $user, NotificationToken $token): bool
    {
        return $token->user_id === $user->id;
    }
}
