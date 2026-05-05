<?php

namespace App\Modules\AI\Application\Services;

use App\Models\User;
use App\Modules\AI\Domain\Enums\AiSafetyEventType;
use App\Modules\AI\Domain\Enums\AiSafetySeverity;
use App\Modules\AI\Infrastructure\Models\AiConversation;
use App\Modules\AI\Infrastructure\Models\AiMessage;
use App\Modules\AI\Infrastructure\Models\AiSafetyEvent;

class AiSafetyEventService
{
    public function create(
        User $patient,
        AiSafetyEventType $type,
        AiSafetySeverity $severity,
        string $description,
        ?AiConversation $conversation = null,
        ?AiMessage $message = null,
        array $metadata = [],
    ): AiSafetyEvent {
        return AiSafetyEvent::query()->create([
            'conversation_id' => $conversation?->id,
            'message_id' => $message?->id,
            'patient_user_id' => $patient->id,
            'event_type' => $type,
            'severity' => $severity,
            'description' => $description,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
