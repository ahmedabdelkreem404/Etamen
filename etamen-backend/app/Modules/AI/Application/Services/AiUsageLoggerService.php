<?php

namespace App\Modules\AI\Application\Services;

use App\Models\User;
use App\Modules\AI\Application\DTOs\AiProviderResponse;
use App\Modules\AI\Domain\Enums\AiProvider;
use App\Modules\AI\Infrastructure\Models\AiConversation;
use App\Modules\AI\Infrastructure\Models\AiUsageLog;

class AiUsageLoggerService
{
    public function logSuccess(User $patient, AiConversation $conversation, AiProviderResponse $response, int $latencyMs): AiUsageLog
    {
        return AiUsageLog::query()->create([
            'patient_user_id' => $patient->id,
            'conversation_id' => $conversation->id,
            'provider' => $response->provider,
            'model' => $response->model,
            'prompt_tokens' => $response->promptTokens,
            'completion_tokens' => $response->completionTokens,
            'total_tokens' => $response->totalTokens,
            'latency_ms' => $latencyMs,
            'success' => true,
            'metadata' => $this->sanitizeMetadata($response->rawMetadata),
            'created_at' => now(),
        ]);
    }

    public function logFailure(
        User $patient,
        ?AiConversation $conversation,
        AiProvider $provider,
        ?string $model,
        string $errorCode,
        array $metadata = [],
    ): AiUsageLog {
        return AiUsageLog::query()->create([
            'patient_user_id' => $patient->id,
            'conversation_id' => $conversation?->id,
            'provider' => $provider,
            'model' => $model,
            'success' => false,
            'error_code' => $errorCode,
            'metadata' => $this->sanitizeMetadata($metadata),
            'created_at' => now(),
        ]);
    }

    private function sanitizeMetadata(array $metadata): array
    {
        foreach ($metadata as $key => $value) {
            $normalizedKey = strtolower((string) $key);

            if (
                str_contains($normalizedKey, 'key')
                || str_contains($normalizedKey, 'secret')
                || str_contains($normalizedKey, 'token')
                || str_contains($normalizedKey, 'authorization')
                || str_contains($normalizedKey, 'raw')
                || str_contains($normalizedKey, 'content')
                || str_contains($normalizedKey, 'response')
            ) {
                unset($metadata[$key]);

                continue;
            }

            if (is_array($value)) {
                $metadata[$key] = $this->sanitizeMetadata($value);
            }
        }

        return $metadata;
    }
}
