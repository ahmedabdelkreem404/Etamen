<?php

namespace App\Modules\AI\Policies;

use App\Models\User;
use App\Modules\AI\Infrastructure\Models\AiMessage;

class AiMessagePolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasAnyRole(['admin', 'super_admin']) ? true : null;
    }

    public function view(User $user, AiMessage $message): bool
    {
        return $message->patient_user_id === $user->id;
    }
}
