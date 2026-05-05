<?php

namespace App\Modules\AI\Application\Services;

use App\Models\User;
use App\Modules\AI\Domain\Enums\AiMessageRole;
use App\Modules\AI\Domain\Enums\AiProvider;
use App\Modules\AI\Domain\Enums\AiSafetyClassification;
use App\Modules\AI\Infrastructure\Models\AiConversation;
use App\Modules\AI\Infrastructure\Models\AiMessage;

class AiMessageService
{
    public function createUserMessage(
        AiConversation $conversation,
        User $patient,
        string $content,
        AiSafetyClassification $classification,
        array $metadata = [],
    ): AiMessage {
        return $this->createMessage(
            conversation: $conversation,
            patient: $patient,
            role: AiMessageRole::User,
            content: $content,
            classification: $classification,
            wasRefused: false,
            provider: null,
            tokenCount: null,
            metadata: $metadata,
        );
    }

    public function createAssistantMessage(
        AiConversation $conversation,
        User $patient,
        string $content,
        AiSafetyClassification $classification,
        bool $wasRefused = false,
        ?AiProvider $provider = null,
        ?int $tokenCount = null,
        array $metadata = [],
    ): AiMessage {
        return $this->createMessage(
            conversation: $conversation,
            patient: $patient,
            role: AiMessageRole::Assistant,
            content: $content,
            classification: $classification,
            wasRefused: $wasRefused,
            provider: $provider,
            tokenCount: $tokenCount,
            metadata: $metadata,
        );
    }

    private function createMessage(
        AiConversation $conversation,
        User $patient,
        AiMessageRole $role,
        string $content,
        AiSafetyClassification $classification,
        bool $wasRefused,
        ?AiProvider $provider,
        ?int $tokenCount,
        array $metadata,
    ): AiMessage {
        $message = AiMessage::query()->create([
            'conversation_id' => $conversation->id,
            'patient_user_id' => $patient->id,
            'role' => $role,
            'content' => $content,
            'safety_classification' => $classification,
            'was_refused' => $wasRefused,
            'provider' => $provider,
            'token_count' => $tokenCount,
            'metadata' => $metadata,
        ]);

        $conversation->forceFill(['last_message_at' => now()])->save();

        return $message;
    }
}
