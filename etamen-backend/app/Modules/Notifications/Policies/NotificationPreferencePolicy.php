<?php

namespace App\Modules\Notifications\Policies;

use App\Models\User;
use App\Modules\Notifications\Infrastructure\Models\NotificationPreference;

class NotificationPreferencePolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasAnyRole(['admin', 'super_admin']) ? true : null;
    }

    public function view(User $user, NotificationPreference $preference): bool
    {
        return $preference->user_id === $user->id;
    }

    public function update(User $user, NotificationPreference $preference): bool
    {
        return $preference->user_id === $user->id;
    }
}
