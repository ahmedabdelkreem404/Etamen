<?php

namespace App\Modules\AI\Application\Services;

use App\Models\User;
use App\Modules\AI\Domain\Enums\AiSafetyEventType;
use App\Modules\AI\Domain\Enums\AiSafetySeverity;
use App\Modules\AI\Infrastructure\Models\AiConversation;
use App\Modules\AI\Infrastructure\Models\AiSafetyEvent;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AiRateLimitService
{
    public function assertCanSend(User $patient, ?AiConversation $conversation = null): void
    {
        $key = $this->messageKey($patient);
        $limit = max(1, (int) config('ai.max_messages_per_hour', 20));

        if (! RateLimiter::tooManyAttempts($key, $limit)) {
            return;
        }

        AiSafetyEvent::query()->create([
            'conversation_id' => $conversation?->id,
            'patient_user_id' => $patient->id,
            'event_type' => AiSafetyEventType::RateLimitHit,
            'severity' => AiSafetySeverity::Medium,
            'description' => 'AI message hourly rate limit exceeded.',
            'metadata' => ['limit' => $limit],
            'created_at' => now(),
        ]);

        throw ValidationException::withMessages([
            'rate_limit' => ['AI message limit exceeded. Please try again later.'],
        ]);
    }

    public function hit(User $patient): void
    {
        RateLimiter::hit($this->messageKey($patient), 3600);
    }

    public function assertCanCreateConversation(User $patient): void
    {
        $key = 'ai:conversations:'.$patient->id.':'.now()->toDateString();
        $limit = max(1, (int) config('ai.max_conversations_per_day', 20));

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            AiSafetyEvent::query()->create([
                'patient_user_id' => $patient->id,
                'event_type' => AiSafetyEventType::RateLimitHit,
                'severity' => AiSafetySeverity::Medium,
                'description' => 'AI conversation daily rate limit exceeded.',
                'metadata' => ['limit' => $limit],
                'created_at' => now(),
            ]);

            throw ValidationException::withMessages([
                'rate_limit' => ['AI conversation limit exceeded. Please try again later.'],
            ]);
        }

        RateLimiter::hit($key, 86400);
    }

    private function messageKey(User $patient): string
    {
        return 'ai:messages:'.$patient->id;
    }
}
