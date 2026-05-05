<?php

namespace App\Modules\AI\Application\Services;

use App\Models\User;
use App\Modules\AI\Domain\Enums\AiConversationStatus;
use App\Modules\AI\Domain\Enums\AiLanguage;
use App\Modules\AI\Domain\Enums\AiProvider;
use App\Modules\AI\Domain\Enums\AiSafetyLevel;
use App\Modules\AI\Infrastructure\Models\AiConversation;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class AiConversationService
{
    public function __construct(
        private readonly AiProviderManager $providers,
        private readonly AiRateLimitService $rateLimits,
        private readonly AuditLogService $auditLogs,
    ) {}

    public function create(User $patient, array $data = []): AiConversation
    {
        $this->rateLimits->assertCanCreateConversation($patient);

        return DB::transaction(function () use ($patient, $data): AiConversation {
            $provider = $this->resolveProvider($data);

            $conversation = AiConversation::query()->create([
                'patient_user_id' => $patient->id,
                'title' => $data['title'] ?? null,
                'status' => AiConversationStatus::Active,
                'provider' => $provider,
                'language' => AiLanguage::tryFrom((string) ($data['language'] ?? 'ar')) ?? AiLanguage::Arabic,
                'context_enabled' => array_key_exists('context_enabled', $data)
                    ? (bool) $data['context_enabled']
                    : (bool) config('ai.context_enabled', true),
                'safety_level' => AiSafetyLevel::Strict,
                'metadata' => $this->safeMetadata($data['metadata'] ?? null),
            ]);

            $this->auditLogs->log('ai_conversation.created', $conversation, $patient, metadata: [
                'provider' => $provider->value,
            ]);

            return $conversation->refresh();
        });
    }

    public function update(User $patient, AiConversation $conversation, array $data): AiConversation
    {
        return DB::transaction(function () use ($patient, $conversation, $data): AiConversation {
            $before = $conversation->getAttributes();
            $payload = [];

            if (array_key_exists('title', $data)) {
                $payload['title'] = $data['title'];
            }

            if (array_key_exists('status', $data)) {
                $payload['status'] = AiConversationStatus::from($data['status']);
            }

            if (array_key_exists('context_enabled', $data)) {
                $payload['context_enabled'] = (bool) $data['context_enabled'];
            }

            if ($payload !== []) {
                $conversation->forceFill($payload)->save();
            }

            $this->auditLogs->log('ai_conversation.updated', $conversation, $patient, before: $before, after: $conversation->getAttributes());

            return $conversation->refresh();
        });
    }

    public function archive(User $patient, AiConversation $conversation): AiConversation
    {
        return DB::transaction(function () use ($patient, $conversation): AiConversation {
            $before = $conversation->getAttributes();
            $conversation->forceFill(['status' => AiConversationStatus::Archived])->save();
            $this->auditLogs->log('ai_conversation.archived', $conversation, $patient, before: $before, after: $conversation->getAttributes());

            return $conversation->refresh();
        });
    }

    public function toggleContext(User $patient, AiConversation $conversation, bool $enabled): AiConversation
    {
        return DB::transaction(function () use ($patient, $conversation, $enabled): AiConversation {
            $before = $conversation->getAttributes();
            $conversation->forceFill(['context_enabled' => $enabled])->save();
            $this->auditLogs->log('ai_conversation.context_toggled', $conversation, $patient, before: $before, after: $conversation->getAttributes());

            return $conversation->refresh();
        });
    }

    private function resolveProvider(array $data): AiProvider
    {
        $requested = isset($data['provider']) ? AiProvider::tryFrom((string) $data['provider']) : null;

        if ($requested && $requested !== AiProvider::Fake) {
            return $requested;
        }

        return $this->providers->defaultProvider();
    }

    private function safeMetadata(mixed $metadata): ?array
    {
        return is_array($metadata) ? $metadata : null;
    }
}
