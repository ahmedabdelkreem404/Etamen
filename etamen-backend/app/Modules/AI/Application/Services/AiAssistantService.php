<?php

namespace App\Modules\AI\Application\Services;

use App\Models\User;
use App\Modules\AI\Application\DTOs\AiProviderResponse;
use App\Modules\AI\Domain\Enums\AiConversationStatus;
use App\Modules\AI\Domain\Enums\AiLanguage;
use App\Modules\AI\Domain\Enums\AiMessageRole;
use App\Modules\AI\Domain\Enums\AiSafetyClassification;
use App\Modules\AI\Domain\Enums\AiSafetyEventType;
use App\Modules\AI\Domain\Enums\AiSafetySeverity;
use App\Modules\AI\Infrastructure\Models\AiConversation;
use App\Modules\AI\Infrastructure\Models\AiMessage;
use App\Modules\AI\Infrastructure\Models\AiSafetyEvent;
use App\Modules\AI\Infrastructure\Providers\ProviderUnavailableException;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class AiAssistantService
{
    public function __construct(
        private readonly AiSafetyGuardService $safetyGuard,
        private readonly AiContextBuilderService $contextBuilder,
        private readonly AiProviderManager $providerManager,
        private readonly AiUsageLoggerService $usageLogger,
        private readonly AiRateLimitService $rateLimits,
        private readonly AiMessageService $messages,
        private readonly AuditLogService $auditLogs,
    ) {}

    public function send(User $patient, AiConversation $conversation, array $data): AiMessage
    {
        if ($conversation->status !== AiConversationStatus::Active) {
            throw ValidationException::withMessages([
                'conversation' => ['Archived or blocked conversations cannot receive new messages.'],
            ]);
        }

        $this->rateLimits->assertCanSend($patient, $conversation);
        $this->rateLimits->hit($patient);

        $language = isset($data['language'])
            ? (AiLanguage::tryFrom((string) $data['language']) ?? $conversation->language)
            : $conversation->language;

        $decision = $this->safetyGuard->inspect($data['content'], $language);

        $userMessage = DB::transaction(function () use ($patient, $conversation, $data, $decision): AiMessage {
            $message = $this->messages->createUserMessage(
                conversation: $conversation,
                patient: $patient,
                content: $data['content'],
                classification: $decision->classification,
                metadata: is_array($data['metadata'] ?? null) ? $data['metadata'] : [],
            );

            $this->auditLogs->log('ai_message.sent', $message, $patient, metadata: [
                'conversation_id' => $conversation->id,
                'classification' => $decision->classification->value,
            ]);

            return $message;
        });

        if ($decision->shouldRefuse) {
            return $this->createRefusal($patient, $conversation, $userMessage, $decision->safeResponse ?? $this->safetyGuard->safetyLine($language), $decision->classification, $decision->eventType, $decision->severity);
        }

        $context = $this->contextBuilder->buildForConversation($conversation);
        $providerMessages = $this->providerMessages($conversation, $userMessage, $context, $language);
        $provider = $conversation->provider;
        $startedAt = microtime(true);

        try {
            $response = $this->providerManager->provider($provider)->send($providerMessages, [
                'max_tokens' => (int) config('ai.max_tokens_per_response', 800),
            ]);
        } catch (ProviderUnavailableException $exception) {
            return $this->providerFailed($patient, $conversation, $provider->value.'_unavailable', $exception->getMessage());
        } catch (Throwable $exception) {
            return $this->providerFailed($patient, $conversation, $provider->value.'_error', $exception->getMessage());
        }

        $latencyMs = (int) round((microtime(true) - $startedAt) * 1000);
        $this->usageLogger->logSuccess($patient, $conversation, $response, $latencyMs);

        $postCheck = $this->safetyGuard->inspect($response->content, $language);

        if ($postCheck->shouldRefuse) {
            return $this->createBlockedProviderResponse(
                patient: $patient,
                conversation: $conversation,
                response: $response,
                contextIncluded: $context !== null,
                content: $postCheck->safeResponse ?? $this->safetyGuard->safetyLine($language),
                classification: $postCheck->classification,
                eventType: $postCheck->eventType ?? AiSafetyEventType::RefusalTriggered,
                severity: $postCheck->severity,
            );
        }

        return DB::transaction(function () use ($patient, $conversation, $response, $context): AiMessage {
            $assistant = $this->messages->createAssistantMessage(
                conversation: $conversation,
                patient: $patient,
                content: $this->safeAssistantContent($response),
                classification: AiSafetyClassification::Safe,
                wasRefused: false,
                provider: $response->provider,
                tokenCount: $response->totalTokens,
                metadata: [
                    'model' => $response->model,
                    'context_included' => $context !== null,
                    'provider_metadata' => $this->sanitizeProviderMetadata($response->rawMetadata),
                ],
            );

            $this->auditLogs->log('ai_assistant.response_generated', $assistant, $patient, metadata: [
                'conversation_id' => $conversation->id,
                'provider' => $response->provider->value,
            ]);

            return $assistant;
        });
    }

    private function createBlockedProviderResponse(
        User $patient,
        AiConversation $conversation,
        AiProviderResponse $response,
        bool $contextIncluded,
        string $content,
        AiSafetyClassification $classification,
        AiSafetyEventType $eventType,
        AiSafetySeverity $severity,
    ): AiMessage {
        return DB::transaction(function () use ($patient, $conversation, $response, $contextIncluded, $content, $classification, $eventType, $severity): AiMessage {
            $assistant = $this->messages->createAssistantMessage(
                conversation: $conversation,
                patient: $patient,
                content: $content,
                classification: $classification,
                wasRefused: true,
                provider: $response->provider,
                tokenCount: $response->totalTokens,
                metadata: [
                    'source' => 'post_response_safety_guard',
                    'model' => $response->model,
                    'context_included' => $contextIncluded,
                    'provider_metadata' => $this->sanitizeProviderMetadata($response->rawMetadata),
                ],
            );

            AiSafetyEvent::query()->create([
                'conversation_id' => $conversation->id,
                'message_id' => $assistant->id,
                'patient_user_id' => $patient->id,
                'event_type' => $eventType,
                'severity' => $severity,
                'description' => 'AI provider response was blocked by post-response safety guard.',
                'metadata' => [
                    'classification' => $classification->value,
                    'provider' => $response->provider->value,
                    'model' => $response->model,
                ],
                'created_at' => now(),
            ]);

            $this->auditLogs->log('ai_safety.provider_response_blocked', $assistant, $patient, metadata: [
                'conversation_id' => $conversation->id,
                'classification' => $classification->value,
                'provider' => $response->provider->value,
            ]);

            return $assistant;
        });
    }

    private function createRefusal(
        User $patient,
        AiConversation $conversation,
        AiMessage $userMessage,
        string $content,
        AiSafetyClassification $classification,
        ?AiSafetyEventType $eventType,
        AiSafetySeverity $severity,
    ): AiMessage {
        return DB::transaction(function () use ($patient, $conversation, $userMessage, $content, $classification, $eventType, $severity): AiMessage {
            $assistant = $this->messages->createAssistantMessage(
                conversation: $conversation,
                patient: $patient,
                content: $content,
                classification: $classification,
                wasRefused: true,
                metadata: ['source' => 'safety_guard'],
            );

            if ($eventType) {
                AiSafetyEvent::query()->create([
                    'conversation_id' => $conversation->id,
                    'message_id' => $userMessage->id,
                    'patient_user_id' => $patient->id,
                    'event_type' => $eventType,
                    'severity' => $severity,
                    'description' => 'AI safety guard returned a local safe response.',
                    'metadata' => ['classification' => $classification->value],
                    'created_at' => now(),
                ]);
            }

            $this->auditLogs->log('ai_safety.refusal_triggered', $assistant, $patient, metadata: [
                'conversation_id' => $conversation->id,
                'classification' => $classification->value,
            ]);

            return $assistant;
        });
    }

    private function providerFailed(User $patient, AiConversation $conversation, string $errorCode, string $message): AiMessage
    {
        $this->usageLogger->logFailure($patient, $conversation, $conversation->provider, null, $errorCode, [
            'error' => str($message)->limit(180)->toString(),
        ]);

        return DB::transaction(function () use ($patient, $conversation, $errorCode): AiMessage {
            AiSafetyEvent::query()->create([
                'conversation_id' => $conversation->id,
                'patient_user_id' => $patient->id,
                'event_type' => AiSafetyEventType::ProviderError,
                'severity' => AiSafetySeverity::Medium,
                'description' => 'AI provider unavailable or failed safely.',
                'metadata' => ['error_code' => $errorCode],
                'created_at' => now(),
            ]);

            $assistant = $this->messages->createAssistantMessage(
                conversation: $conversation,
                patient: $patient,
                content: 'المساعد غير متاح مؤقتًا، جرّب لاحقًا.',
                classification: AiSafetyClassification::Unknown,
                wasRefused: true,
                metadata: ['error_code' => $errorCode],
            );

            $this->auditLogs->log('ai_provider.error', $assistant, $patient, metadata: [
                'conversation_id' => $conversation->id,
                'error_code' => $errorCode,
            ]);

            return $assistant;
        });
    }

    private function providerMessages(AiConversation $conversation, AiMessage $userMessage, ?array $context, AiLanguage $language): array
    {
        $messages = [[
            'role' => AiMessageRole::System->value,
            'content' => $this->loadSystemPrompt($language),
        ]];

        if ($context !== null) {
            $messages[] = [
                'role' => AiMessageRole::System->value,
                'content' => "Context is user-provided and may be incomplete. Do not diagnose.\n".json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ];
        }

        $recentMessages = $conversation->messages()
            ->whereIn('role', [AiMessageRole::User->value, AiMessageRole::Assistant->value])
            ->latest('id')
            ->limit(10)
            ->get()
            ->reverse();

        foreach ($recentMessages as $message) {
            $messages[] = [
                'role' => $message->role->value,
                'content' => $message->content,
            ];
        }

        if (! $recentMessages->contains('id', $userMessage->id)) {
            $messages[] = [
                'role' => AiMessageRole::User->value,
                'content' => $userMessage->content,
            ];
        }

        return $messages;
    }

    private function loadSystemPrompt(AiLanguage $language): string
    {
        $path = app_path('Modules/AI/Infrastructure/Prompts/'.($language === AiLanguage::English ? 'en' : 'ar').'/system_medical_safety.md');

        return file_exists($path) ? (string) file_get_contents($path) : $this->safetyGuard->safetyLine($language);
    }

    private function safeAssistantContent(AiProviderResponse $response): string
    {
        $content = trim($response->content);

        return $content !== '' ? $content : 'المساعد غير متاح مؤقتًا، جرّب لاحقًا.';
    }

    private function sanitizeProviderMetadata(array $metadata): array
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
                $metadata[$key] = $this->sanitizeProviderMetadata($value);
            }
        }

        return $metadata;
    }
}
