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
use App\Modules\Medications\Domain\Enums\MedicationLogAction;
use App\Modules\Medications\Infrastructure\Models\MedicationLog;
use App\Modules\Medications\Infrastructure\Models\MedicationReminder;
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

        $this->assertDatabaseHas('audit_logs', ['action' => 'ai_conversation.created']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'ai_conversation.archived']);
        $this->getJson('/api/v1/ai-public')->assertNotFound();
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
    }
}
