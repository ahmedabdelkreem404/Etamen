<?php

namespace App\Modules\Notifications\Policies;

use App\Models\User;
use App\Modules\Notifications\Infrastructure\Models\NotificationTemplate;

class NotificationTemplatePolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasAnyRole(['admin', 'super_admin']) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, NotificationTemplate $template): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, NotificationTemplate $template): bool
    {
        return false;
    }
}
