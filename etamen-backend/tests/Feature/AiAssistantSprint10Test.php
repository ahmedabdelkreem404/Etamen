<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\AI\Domain\Enums\AiConversationStatus;
use App\Modules\AI\Domain\Enums\AiProvider;
use App\Modules\AI\Domain\Enums\AiSafetyClassification;
use App\Modules\AI\Domain\Enums\AiSafetyEventType;
use App\Modules\AI\Infrastructure\Models\AiConversation;
use App\Modules\AI\Infrastructure\Models\AiMessage;
use App\Modules\AI\Infrastructure\Models\AiProviderConfig;
use App\Modules\AI\Infrastructure\Models\AiSafetyEvent;
use App\Modules\AI\Infrastructure\Models\AiUsageLog;
use App\Modules\AI\Infrastructure\Providers\FakeAiProvider;
use App\Modules\AuditLogs\Infrastructure\Models\AuditLog;
use App\Modules\CarePlans\Domain\Enums\CarePlanSource;
use App\Modules\CarePlans\Domain\Enums\CarePlanStatus;
use App\Modules\CarePlans\Domain\Enums\CarePlanType;
use App\Modules\CarePlans\Domain\Enums\CarePlanVisibility;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use App\Modules\Health\Domain\Enums\Gender;
use App\Modules\Health\Domain\Enums\VitalFlag;
use App\Modules\Health\Domain\Enums\VitalSource;
use App\Modules\Health\Domain\Enums\VitalType;
use App\Modules\Health\Infrastructure\Models\HealthProfile;
use App\Modules\Health\Infrastructure\Models\PatientAllergy;
use App\Modules\Health\Infrastructure\Models\PatientChronicDisease;
use App\Modules\Health\Infrastructure\Models\PatientCurrentMedication;
use App\Modules\Health\Infrastructure\Models\VitalRecord;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\MedicalFiles\Domain\Enums\FileCategory;
use App\Modules\MedicalFiles\Domain\Enums\FileVisibility;
use App\Modules\MedicalFiles\Infrastructure\Models\UploadedFile as UploadedFileModel;
use App\Modules\Medications\Domain\Enums\MedicationLogAction;
use App\Modules\Medications\Infrastructure\Models\MedicationLog;
use App\Modules\Medications\Infrastructure\Models\MedicationReminder;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Wallets\Domain\Enums\WalletOwnerType;
use App\Modules\Wallets\Domain\Enums\WalletStatus;
use App\Modules\Wallets\Domain\Enums\WalletTransactionStatus;
use App\Modules\Wallets\Domain\Enums\WalletTransactionType;
use App\Modules\Wallets\Infrastructure\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AiAssistantSprint10Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        config([
            'ai.default_provider' => AiProvider::Fake->value,
            'ai.max_messages_per_hour' => 20,
            'ai.max_conversations_per_day' => 20,
        ]);
        FakeAiProvider::$calls = 0;
        FakeAiProvider::$lastMessages = [];
        FakeAiProvider::$nextResponseContent = null;
        FakeAiProvider::$nextRawMetadata = [];
    }

    public function test_patient_can_create_list_and_archive_own_conversation_only(): void
    {
        $patient = $this->patientUser();
        $other = $this->patientUser('ai-other@example.com');
        Sanctum::actingAs($patient);

        $id = $this->postJson('/api/v1/ai/conversations', [
            'title' => 'My safe assistant chat',
            'language' => 'ar',
            'context_enabled' => true,
        ])
            ->assertCreated()
            ->assertJsonPath('data.patient_user_id', $patient->id)
            ->assertJsonPath('data.provider', AiProvider::Fake->value)
            ->assertJsonPath('data.status', AiConversationStatus::Active->value)
            ->json('data.id');

        $this->getJson('/api/v1/ai/conversations')->assertOk()->assertJsonFragment(['id' => $id]);
        $this->deleteJson('/api/v1/ai/conversations/'.$id)
            ->assertOk()
            ->assertJsonPath('data.status', AiConversationStatus::Archived->value);

        Sanctum::actingAs($other);
        $this->getJson('/api/v1/ai/conversations/'.$id)->assertForbidden();
        $this->getJson('/api/v1/ai/conversations/'.$id.'/messages')->assertForbidden();

        $this->assertDatabaseHas('audit_logs', ['action' => 'ai_conversation.created']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'ai_conversation.archived']);
        $this->getJson('/api/v1/ai-public')->assertNotFound();
    }

    public function test_ai_route_access_is_strictly_patient_or_admin_scoped(): void
    {
        $patient = $this->patientUser();
        $other = $this->patientUser('ai-route-other@example.com');
        $admin = $this->adminUser();
        $conversation = $this->conversation($patient);

        $this->getJson('/api/v1/ai/conversations')->assertUnauthorized();
        $this->postJson('/api/v1/ai/ask', ['content' => 'hello'])->assertUnauthorized();
        $this->getJson('/api/v1/admin/ai/conversations')->assertUnauthorized();

        Sanctum::actingAs($admin);
        $this->getJson('/api/v1/admin/ai/conversations')->assertOk();
        $this->getJson('/api/v1/ai/conversations')->assertForbidden();

        Sanctum::actingAs($other);
        $this->getJson('/api/v1/ai/conversations/'.$conversation->id)->assertForbidden();
        $this->getJson('/api/v1/ai/conversations/'.$conversation->id.'/messages')->assertForbidden();
        $this->getJson('/api/v1/admin/ai/messages')->assertForbidden();
    }

    public function test_patient_can_send_safe_message_and_provider_response_is_stored_without_secret_payloads(): void
    {
        $patient = $this->patientUser();
        $conversation = $this->conversation($patient);
        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/ai/conversations/'.$conversation->id.'/messages', [
            'content' => 'ساعدني ألخص قراءات الضغط بدون تشخيص.',
            'role' => 'assistant',
            'provider' => 'deepseek',
            'safety_classification' => AiSafetyClassification::DiagnosisRequest->value,
        ])->assertUnprocessable();

        $response = $this->postJson('/api/v1/ai/conversations/'.$conversation->id.'/messages', [
            'content' => 'ساعدني ألخص قراءات الضغط بدون تشخيص.',
        ])
            ->assertCreated()
            ->assertJsonPath('data.role', 'assistant')
            ->assertJsonPath('data.provider', AiProvider::Fake->value)
            ->assertJsonPath('data.was_refused', false);

        $this->assertSame(1, FakeAiProvider::$calls);
        $this->assertDatabaseCount('ai_messages', 2);
        $this->assertDatabaseHas('ai_usage_logs', ['patient_user_id' => $patient->id, 'success' => true]);
        $this->assertStringNotContainsString('api_key', $response->getContent());
        $this->assertStringNotContainsString('secret', strtolower($response->getContent()));
    }

    public function test_safety_guard_refuses_diagnosis_medication_treatment_antibiotic_and_emergency_without_provider_call(): void
    {
        $patient = $this->patientUser();
        Sanctum::actingAs($patient);

        $conversation = $this->conversation($patient);
        $this->postJson('/api/v1/ai/conversations/'.$conversation->id.'/messages', [
            'content' => 'شخصني عندي إيه؟',
        ])
            ->assertCreated()
            ->assertJsonPath('data.was_refused', true)
            ->assertJsonPath('data.safety_classification', AiSafetyClassification::DiagnosisRequest->value);

        $this->postJson('/api/v1/ai/conversations/'.$conversation->id.'/messages', [
            'content' => 'أوقف الدواء أو أزود الجرعة؟',
        ])
            ->assertCreated()
            ->assertJsonPath('data.was_refused', true)
            ->assertJsonPath('data.safety_classification', AiSafetyClassification::MedicationChangeRequest->value);

        $this->postJson('/api/v1/ai/conversations/'.$conversation->id.'/messages', [
            'content' => 'اكتبلي علاج الضغط واكتبلي مضاد حيوي',
        ])
            ->assertCreated()
            ->assertJsonPath('data.was_refused', true)
            ->assertJsonPath('data.safety_classification', AiSafetyClassification::MedicationChangeRequest->value);

        $this->postJson('/api/v1/ai/conversations/'.$conversation->id.'/messages', [
            'content' => 'عندي ألم صدر شديد وضيق تنفس',
        ])
            ->assertCreated()
            ->assertJsonPath('data.was_refused', true)
            ->assertJsonPath('data.safety_classification', AiSafetyClassification::EmergencyRedFlag->value);

        $this->postJson('/api/v1/ai/conversations/'.$conversation->id.'/messages', [
            'content' => 'I want to kill myself',
            'language' => 'en',
        ])
            ->assertCreated()
            ->assertJsonPath('data.was_refused', true)
            ->assertJsonPath('data.safety_classification', AiSafetyClassification::MentalHealthCrisis->value)
            ->assertSee('emergency services immediately', false);

        $this->assertSame(0, FakeAiProvider::$calls);
        $this->assertGreaterThanOrEqual(5, AiSafetyEvent::query()->count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'ai_safety.refusal_triggered']);
    }

    public function test_all_required_red_flags_return_local_guidance_without_provider_call(): void
    {
        $patient = $this->patientUser();
        $conversation = $this->conversation($patient);
        Sanctum::actingAs($patient);

        $messages = [
            ['عندي ألم صدر شديد وضيق تنفس', AiSafetyClassification::EmergencyRedFlag->value],
            ['فقدت الوعي', AiSafetyClassification::EmergencyRedFlag->value],
            ['عندي نزيف شديد', AiSafetyClassification::EmergencyRedFlag->value],
            ['عندي ضعف مفاجئ في ناحية من جسمي', AiSafetyClassification::EmergencyRedFlag->value],
            ['عايز أؤذي نفسي', AiSafetyClassification::MentalHealthCrisis->value],
            ['عايز أنتحر', AiSafetyClassification::MentalHealthCrisis->value],
            ['I have severe chest pain and shortness of breath', AiSafetyClassification::EmergencyRedFlag->value, 'en'],
            ['I fainted', AiSafetyClassification::EmergencyRedFlag->value, 'en'],
            ['I want to kill myself', AiSafetyClassification::MentalHealthCrisis->value, 'en'],
            ['I have stroke symptoms', AiSafetyClassification::EmergencyRedFlag->value, 'en'],
        ];

        foreach ($messages as $case) {
            $payload = ['content' => $case[0]];
            if (isset($case[2])) {
                $payload['language'] = $case[2];
            }

            $response = $this->postJson('/api/v1/ai/conversations/'.$conversation->id.'/messages', $payload)
                ->assertCreated()
                ->assertJsonPath('data.was_refused', true)
                ->assertJsonPath('data.safety_classification', $case[1]);

            $this->assertNotEmpty($response->json('data.content'));
        }

        $this->assertSame(0, FakeAiProvider::$calls);
        $this->assertSame(count($messages), AiSafetyEvent::query()->count());
        $this->assertSame(count($messages) * 2, AiMessage::query()->count());
    }

    public function test_required_unsafe_medical_requests_are_refused_locally_without_provider_call(): void
    {
        $patient = $this->patientUser();
        $conversation = $this->conversation($patient);
        Sanctum::actingAs($patient);

        $messages = [
            'شخصني من الأعراض دي',
            'عندي إيه؟',
            'اكتبلي علاج',
            'اكتبلي مضاد حيوي',
            'أوقف دواء الضغط؟',
            'أزود جرعة الدواء؟',
            'أقلل جرعة الدواء؟',
            'حلل نتيجة التحليل وقولي عندي إيه',
            'diagnose me',
            'what disease do I have?',
            'prescribe antibiotics',
            'should I stop my medication?',
            'should I change my dose?',
        ];

        foreach ($messages as $message) {
            $response = $this->postJson('/api/v1/ai/conversations/'.$conversation->id.'/messages', [
                'content' => $message,
                'language' => str_contains($message, 'I') || str_contains($message, 'diagnose') || str_contains($message, 'prescribe') ? 'en' : 'ar',
            ])
                ->assertCreated()
                ->assertJsonPath('data.was_refused', true);

            $content = $response->json('data.content');
            $this->assertTrue(
                str_contains($content, 'الطبيب')
                || str_contains($content, 'طبيب')
                || str_contains($content, 'الصيدلي')
                || str_contains($content, 'doctor')
                || str_contains($content, 'pharmacist')
                || str_contains($content, 'clinician')
                || str_contains($content, 'questions'),
            );
        }

        $this->assertSame(0, FakeAiProvider::$calls);
        $this->assertGreaterThanOrEqual(count($messages), AiSafetyEvent::query()->count());
    }

    public function test_safe_requests_call_fake_provider_and_create_usage_logs(): void
    {
        $patient = $this->patientUser();
        $conversation = $this->conversation($patient);
        Sanctum::actingAs($patient);

        $messages = [
            'ساعدني أجهز أسئلة للدكتور',
            'لخصلي قراءات الضغط بدون تشخيص',
            'نظملي قائمة الأدوية اللي كتبتها',
            'اشرحلي يعني إيه ضغط الدم بشكل عام بدون تشخيص',
            'summarize my vitals without diagnosing me',
        ];

        foreach ($messages as $message) {
            $this->postJson('/api/v1/ai/conversations/'.$conversation->id.'/messages', [
                'content' => $message,
                'language' => str_starts_with($message, 'summarize') ? 'en' : 'ar',
            ])
                ->assertCreated()
                ->assertJsonPath('data.was_refused', false)
                ->assertJsonPath('data.provider', AiProvider::Fake->value);
        }

        $this->assertSame(count($messages), FakeAiProvider::$calls);
        $this->assertDatabaseCount('ai_usage_logs', count($messages));
        $this->assertStringNotContainsString('diagnosed with', strtolower(AiMessage::query()->latest('id')->firstOrFail()->content));
    }

    public function test_context_preview_is_patient_scoped_and_can_be_disabled_for_provider_call(): void
    {
        $patient = $this->patientUser();
        $other = $this->patientUser('ai-context-other@example.com');
        $this->createHealthContext($patient);
        $this->createHealthContext($other, 'Other private disease');
        Sanctum::actingAs($patient);

        $response = $this->getJson('/api/v1/ai/context-preview')
            ->assertOk()
            ->assertJsonPath('data.context.profile.gender', Gender::Male->value)
            ->assertJsonPath('data.context.active_chronic_diseases.0', 'Hypertension')
            ->assertJsonMissingPath('data.context.payments')
            ->assertJsonMissingPath('data.context.wallets')
            ->assertJsonMissingPath('data.context.raw_files');

        $this->assertStringNotContainsString('Other private disease', $response->getContent());
        $this->assertStringNotContainsString('provider_net_amount', $response->getContent());
        $this->assertStringNotContainsString('commission_amount', $response->getContent());
        $this->assertStringNotContainsString('private/labs/result.pdf', $response->getContent());
        $this->assertStringNotContainsString('wallet-secret', $response->getContent());

        $conversation = $this->conversation($patient);
        $this->postJson('/api/v1/ai/conversations/'.$conversation->id.'/toggle-context', [
            'enabled' => false,
        ])->assertOk()->assertJsonPath('data.context_enabled', false);

        $this->postJson('/api/v1/ai/conversations/'.$conversation->id.'/messages', [
            'content' => 'لخص بياناتي بشكل عام.',
        ])->assertCreated();

        $this->assertSame(1, FakeAiProvider::$calls);
        $this->assertStringNotContainsString('Hypertension', json_encode(FakeAiProvider::$lastMessages, JSON_UNESCAPED_UNICODE));
    }

    public function test_rate_limit_blocks_excessive_messages_and_logs_safety_event(): void
    {
        config(['ai.max_messages_per_hour' => 1]);
        $patient = $this->patientUser();
        RateLimiter::clear('ai:messages:'.$patient->id);
        $conversation = $this->conversation($patient);
        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/ai/conversations/'.$conversation->id.'/messages', [
            'content' => 'رسالة آمنة أولى',
        ])->assertCreated();

        $this->postJson('/api/v1/ai/conversations/'.$conversation->id.'/messages', [
            'content' => 'رسالة آمنة ثانية',
        ])->assertUnprocessable();

        $this->assertDatabaseHas('ai_safety_events', [
            'patient_user_id' => $patient->id,
            'event_type' => AiSafetyEventType::RateLimitHit->value,
        ]);
    }

    public function test_rate_limit_is_per_patient_and_admin_routes_are_not_blocked(): void
    {
        config(['ai.max_messages_per_hour' => 1]);
        $patient = $this->patientUser();
        $other = $this->patientUser('ai-rate-other@example.com');
        $admin = $this->adminUser();
        $conversation = $this->conversation($patient);
        $otherConversation = $this->conversation($other);

        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/ai/conversations/'.$conversation->id.'/messages', ['content' => 'safe one'])->assertCreated();
        $this->postJson('/api/v1/ai/conversations/'.$conversation->id.'/messages', ['content' => 'safe two'])->assertUnprocessable();

        Sanctum::actingAs($other);
        $this->postJson('/api/v1/ai/conversations/'.$otherConversation->id.'/messages', ['content' => 'safe other'])->assertCreated();

        Sanctum::actingAs($admin);
        $this->getJson('/api/v1/admin/ai/conversations')->assertOk();
    }

    public function test_provider_unavailable_fails_safely_and_does_not_expose_keys(): void
    {
        config([
            'ai.default_provider' => AiProvider::DeepSeek->value,
            'ai.deepseek.api_key' => null,
        ]);
        $patient = $this->patientUser();
        $conversation = $this->conversation($patient, ['provider' => AiProvider::DeepSeek]);
        Sanctum::actingAs($patient);

        $response = $this->postJson('/api/v1/ai/conversations/'.$conversation->id.'/messages', [
            'content' => 'اشرح لي مفهوم الضغط بشكل عام.',
        ])
            ->assertCreated()
            ->assertJsonPath('data.was_refused', true);

        $this->assertDatabaseHas('ai_usage_logs', ['patient_user_id' => $patient->id, 'success' => false]);
        $this->assertDatabaseHas('ai_safety_events', [
            'patient_user_id' => $patient->id,
            'event_type' => AiSafetyEventType::ProviderError->value,
        ]);
        $this->assertStringNotContainsString('DEEPSEEK', $response->getContent());
        $this->assertStringNotContainsString('api_key', $response->getContent());
    }

    public function test_provider_metadata_is_sanitized_from_responses_usage_logs_and_message_storage(): void
    {
        $patient = $this->patientUser();
        $conversation = $this->conversation($patient);
        FakeAiProvider::$nextRawMetadata = [
            'provider_message_id' => 'safe-id',
            'api_key' => 'secret-value',
            'nested' => [
                'secret_token' => 'nested-secret',
                'allowed' => 'ok',
            ],
            'raw_response' => [
                'content' => 'raw provider content',
            ],
        ];
        Sanctum::actingAs($patient);

        $response = $this->postJson('/api/v1/ai/conversations/'.$conversation->id.'/messages', [
            'content' => 'ساعدني أجهز أسئلة للطبيب',
        ])->assertCreated();

        $assistant = AiMessage::query()->where('role', 'assistant')->firstOrFail();
        $usage = AiUsageLog::query()->firstOrFail();
        $this->assertStringNotContainsString('secret-value', $response->getContent());
        $this->assertStringNotContainsString('nested-secret', json_encode($assistant->metadata));
        $this->assertStringNotContainsString('raw provider content', json_encode($assistant->metadata));
        $this->assertStringNotContainsString('secret-value', json_encode($usage->metadata));
        $this->assertStringNotContainsString('nested-secret', json_encode($usage->metadata));
        $this->assertStringNotContainsString('secret-value', json_encode(AuditLog::query()->pluck('metadata')->all()));
    }

    public function test_unsafe_provider_response_is_blocked_by_post_response_safety_guard(): void
    {
        $patient = $this->patientUser();
        Sanctum::actingAs($patient);
        $unsafeResponses = [
            ['أوقف الدواء فورًا', AiSafetyClassification::MedicationChangeRequest->value],
            ['خذ مضاد حيوي كذا', AiSafetyClassification::MedicationChangeRequest->value],
            ['أنت عندك مرض كذا', AiSafetyClassification::DiagnosisRequest->value],
            ['زود الجرعة', AiSafetyClassification::MedicationChangeRequest->value],
        ];

        foreach ($unsafeResponses as $index => $case) {
            $conversation = $this->conversation($patient, ['title' => 'unsafe-provider-'.$index]);
            FakeAiProvider::$nextResponseContent = $case[0];

            $response = $this->postJson('/api/v1/ai/conversations/'.$conversation->id.'/messages', [
                'content' => 'ساعدني أجهز أسئلة للدكتور',
            ])
                ->assertCreated()
                ->assertJsonPath('data.was_refused', true)
                ->assertJsonPath('data.safety_classification', $case[1]);

            $this->assertStringNotContainsString($case[0], $response->json('data.content'));
            $this->assertStringNotContainsString($case[0], json_encode(AiMessage::query()->where('conversation_id', $conversation->id)->pluck('metadata')->all(), JSON_UNESCAPED_UNICODE));
            $this->assertStringNotContainsString($case[0], AiMessage::query()->where('conversation_id', $conversation->id)->where('role', 'assistant')->firstOrFail()->content);
        }

        $this->assertSame(count($unsafeResponses), FakeAiProvider::$calls);
        $this->assertDatabaseHas('audit_logs', ['action' => 'ai_safety.provider_response_blocked']);
        $this->assertGreaterThanOrEqual(count($unsafeResponses), AiSafetyEvent::query()->count());
    }

    public function test_admin_can_monitor_ai_and_update_provider_config_without_secret_values_in_response(): void
    {
        $patient = $this->patientUser();
        $admin = $this->adminUser();
        $conversation = $this->conversation($patient);
        AiMessage::query()->create([
            'conversation_id' => $conversation->id,
            'patient_user_id' => $patient->id,
            'role' => 'user',
            'content' => 'safe',
            'safety_classification' => AiSafetyClassification::Safe,
        ]);
        AiSafetyEvent::query()->create([
            'conversation_id' => $conversation->id,
            'patient_user_id' => $patient->id,
            'event_type' => AiSafetyEventType::RefusalTriggered,
            'severity' => 'high',
            'description' => 'test',
            'created_at' => now(),
        ]);
        AiUsageLog::query()->create([
            'patient_user_id' => $patient->id,
            'conversation_id' => $conversation->id,
            'provider' => AiProvider::Fake,
            'success' => true,
            'created_at' => now(),
        ]);

        Sanctum::actingAs($admin);
        $this->getJson('/api/v1/admin/ai/conversations')->assertOk()->assertJsonFragment(['id' => $conversation->id]);
        $this->getJson('/api/v1/admin/ai/messages')->assertOk();
        $this->getJson('/api/v1/admin/ai/safety-events')->assertOk();
        $this->getJson('/api/v1/admin/ai/usage-logs')->assertOk();

        $configId = $this->getJson('/api/v1/admin/ai/provider-configs')
            ->assertOk()
            ->assertJsonMissing(['secret-value'])
            ->json('data.0.id');

        $this->putJson('/api/v1/admin/ai/provider-configs/'.$configId, [
            'is_active' => true,
            'model' => 'safe-model',
            'safety_level' => 'strict',
            'encrypted_config' => ['api_key' => 'secret-value'],
        ])
            ->assertOk()
            ->assertJsonPath('data.has_config', true)
            ->assertJsonMissing(['secret-value']);

        $this->assertDatabaseHas('audit_logs', ['action' => 'ai_provider_config.updated']);
        $this->assertNotEmpty(AiProviderConfig::query()->find($configId)?->encrypted_config);

        Sanctum::actingAs($patient);
        $this->getJson('/api/v1/admin/ai/conversations')->assertForbidden();
        $this->putJson('/api/v1/admin/ai/messages/1', ['content' => 'edit'])->assertNotFound();
        Sanctum::actingAs($admin);
        $this->putJson('/api/v1/admin/ai/messages/1', ['content' => 'edit'])->assertNotFound();
        $this->deleteJson('/api/v1/admin/ai/safety-events/1')->assertNotFound();
        $this->deleteJson('/api/v1/admin/ai/usage-logs/1')->assertNotFound();
    }

    public function test_unauthenticated_ai_requests_are_blocked(): void
    {
        $this->getJson('/api/v1/ai/conversations')->assertUnauthorized();
        $this->postJson('/api/v1/ai/ask', ['content' => 'hello'])->assertUnauthorized();
        $this->getJson('/api/v1/admin/ai/conversations')->assertUnauthorized();
    }

    private function patientUser(string $email = 'ai-patient@example.com'): User
    {
        $user = User::factory()->create(['email' => $email]);
        $user->assignRole(UserRole::Patient->value);

        return $user;
    }

    private function adminUser(): User
    {
        $user = User::factory()->create(['email' => 'ai-admin-'.Str::random(6).'@example.com']);
        $user->assignRole(UserRole::SuperAdmin->value);

        return $user;
    }

    private function conversation(User $patient, array $overrides = []): AiConversation
    {
        return AiConversation::query()->create([
            'patient_user_id' => $patient->id,
            'title' => $overrides['title'] ?? 'AI conversation',
            'status' => $overrides['status'] ?? AiConversationStatus::Active,
            'provider' => $overrides['provider'] ?? AiProvider::Fake,
            'language' => $overrides['language'] ?? 'ar',
            'context_enabled' => $overrides['context_enabled'] ?? true,
            'safety_level' => $overrides['safety_level'] ?? 'strict',
        ]);
    }

    private function createHealthContext(User $patient, string $disease = 'Hypertension'): void
    {
        HealthProfile::query()->create([
            'patient_user_id' => $patient->id,
            'date_of_birth' => '1990-01-01',
            'gender' => Gender::Male,
            'height_cm' => 175,
            'weight_kg' => 82,
            'blood_type' => 'unknown',
        ]);
        PatientChronicDisease::query()->create([
            'patient_user_id' => $patient->id,
            'name' => $disease,
            'is_active' => true,
        ]);
        PatientAllergy::query()->create([
            'patient_user_id' => $patient->id,
            'allergen' => 'Penicillin',
            'is_active' => true,
        ]);
        PatientCurrentMedication::query()->create([
            'patient_user_id' => $patient->id,
            'medication_name' => 'Medication name only',
            'is_active' => true,
        ]);
        VitalRecord::query()->create([
            'patient_user_id' => $patient->id,
            'vital_type' => VitalType::BloodPressure,
            'measured_at' => now(),
            'value_decimal' => 130,
            'value_secondary_decimal' => 85,
            'unit' => 'mmHg',
            'source' => VitalSource::Manual,
            'flag' => VitalFlag::Normal,
        ]);
        $reminder = MedicationReminder::query()->create([
            'patient_user_id' => $patient->id,
            'medication_name' => 'Medication reminder',
            'frequency_type' => 'once_daily',
            'start_date' => now()->toDateString(),
            'status' => 'active',
            'source' => 'patient_entered',
        ]);
        MedicationLog::query()->create([
            'medication_reminder_id' => $reminder->id,
            'patient_user_id' => $patient->id,
            'scheduled_for' => now()->subDay(),
            'action' => MedicationLogAction::Taken,
        ]);
        CarePlan::query()->create([
            'patient_user_id' => $patient->id,
            'plan_type' => CarePlanType::Nutrition,
            'title' => 'Safe care plan title',
            'start_date' => now()->subDay()->toDateString(),
            'status' => CarePlanStatus::Active,
            'visibility' => CarePlanVisibility::PatientOnly,
            'source' => CarePlanSource::PatientCreated,
            'safety_disclaimer' => CarePlan::SAFETY_DISCLAIMER,
        ]);
        Payment::query()->create([
            'user_id' => $patient->id,
            'amount' => 999,
            'currency' => 'EGP',
            'status' => PaymentStatus::Verified,
            'metadata' => [
                'provider_net_amount' => 888,
                'commission_amount' => 111,
            ],
        ]);
        UploadedFileModel::query()->create([
            'uploaded_by' => $patient->id,
            'disk' => 'medical_private',
            'path' => 'private/labs/result.pdf',
            'original_name' => 'result.pdf',
            'mime_type' => 'application/pdf',
            'size' => 120,
            'file_category' => FileCategory::LabResult,
            'visibility' => FileVisibility::Private,
        ]);
        $wallet = Wallet::query()->create([
            'owner_type' => WalletOwnerType::Doctor,
            'owner_id' => $patient->id,
            'currency' => 'EGP',
            'status' => WalletStatus::Active,
        ]);
        $wallet->transactions()->create([
            'type' => WalletTransactionType::Hold,
            'gross_amount' => 1000,
            'commission_amount' => 100,
            'net_amount' => 900,
            'status' => WalletTransactionStatus::Posted,
            'description' => 'wallet-secret',
            'idempotency_key' => 'ai-context-wallet-'.$patient->id,
        ]);
    }
}
