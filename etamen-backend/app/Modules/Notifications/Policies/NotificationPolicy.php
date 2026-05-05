<?php

namespace App\Modules\Notifications\Policies;

use App\Models\User;
use App\Modules\Notifications\Infrastructure\Models\Notification;

class NotificationPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasAnyRole(['admin', 'super_admin']) ? true : null;
    }

    public function view(User $user, Notification $notification): bool
    {
        return $notification->user_id === $user->id;
    }

    public function update(User $user, Notification $notification): bool
    {
        return $notification->user_id === $user->id;
    }

    public function delete(User $user, Notification $notification): bool
    {
        return $notification->user_id === $user->id;
    }
}
