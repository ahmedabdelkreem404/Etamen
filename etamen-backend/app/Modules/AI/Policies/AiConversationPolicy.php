<?php

namespace App\Modules\AI\Policies;

use App\Models\User;
use App\Modules\AI\Infrastructure\Models\AiConversation;

class AiConversationPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasAnyRole(['admin', 'super_admin']) ? true : null;
    }

    public function view(User $user, AiConversation $conversation): bool
    {
        return $conversation->patient_user_id === $user->id;
    }

    public function update(User $user, AiConversation $conversation): bool
    {
        return $conversation->patient_user_id === $user->id;
    }

    public function delete(User $user, AiConversation $conversation): bool
    {
        return $conversation->patient_user_id === $user->id;
    }
}
