<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\AI\Domain\Enums\AiSafetyEventType;
use App\Modules\AI\Domain\Enums\AiSafetySeverity;
use App\Modules\AI\Infrastructure\Models\AiSafetyEvent;
use App\Modules\Appointments\Domain\Enums\AppointmentSlotStatus;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Domain\Enums\ConsultationType;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\Appointments\Infrastructure\Models\AppointmentSlot;
use App\Modules\CarePlans\Domain\Enums\CarePlanSource;
use App\Modules\CarePlans\Domain\Enums\CarePlanStatus;
use App\Modules\CarePlans\Domain\Enums\CarePlanType;
use App\Modules\CarePlans\Domain\Enums\CarePlanVisibility;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Labs\Domain\Enums\LabOrderPaymentStatus;
use App\Modules\Labs\Domain\Enums\LabOrderStatus;
use App\Modules\Labs\Domain\Enums\LabResultStatus;
use App\Modules\Labs\Domain\Enums\LabSampleCollectionMethod;
use App\Modules\Labs\Infrastructure\Models\LabOrder;
use App\Modules\Labs\Infrastructure\Models\LabResult;
use App\Modules\MedicalFiles\Domain\Enums\FileCategory;
use App\Modules\MedicalFiles\Domain\Enums\FileVisibility;
use App\Modules\MedicalFiles\Infrastructure\Models\UploadedFile as UploadedFileModel;
use App\Modules\Medications\Domain\Enums\MedicationNotificationChannel;
use App\Modules\Medications\Domain\Enums\MedicationNotificationStatus;
use App\Modules\Medications\Domain\Enums\MedicationNotificationType;
use App\Modules\Medications\Infrastructure\Models\MedicationNotificationQueue;
use App\Modules\Medications\Infrastructure\Models\MedicationReminder;
use App\Modules\Notifications\Application\Jobs\GenerateAiSafetyAdminAlertsJob;
use App\Modules\Notifications\Application\Jobs\GenerateAppointmentRemindersJob;
use App\Modules\Notifications\Application\Jobs\GenerateCarePlanCheckinRemindersJob;
use App\Modules\Notifications\Application\Jobs\GenerateMedicationNotificationsJob;
use App\Modules\Notifications\Application\Jobs\SendDueNotificationDispatchesJob;
use App\Modules\Notifications\Application\Providers\FakeNotificationProvider;
use App\Modules\Notifications\Application\Services\NotificationDispatchService;
use App\Modules\Notifications\Application\Services\NotificationIntegrationService;
use App\Modules\Notifications\Application\Services\NotificationPayloadSanitizer;
use App\Modules\Notifications\Application\Services\NotificationSchedulerService;
use App\Modules\Notifications\Application\Services\NotificationService;
use App\Modules\Notifications\Application\Services\SchedulerRunService;
use App\Modules\Notifications\Database\Seeders\NotificationTemplateSeeder;
use App\Modules\Notifications\Domain\Enums\NotificationCategory;
use App\Modules\Notifications\Domain\Enums\NotificationChannel;
use App\Modules\Notifications\Domain\Enums\NotificationDeviceType;
use App\Modules\Notifications\Domain\Enums\NotificationDispatchStatus;
use App\Modules\Notifications\Domain\Enums\NotificationPriority;
use App\Modules\Notifications\Domain\Enums\NotificationTokenProvider;
use App\Modules\Notifications\Infrastructure\Models\Notification;
use App\Modules\Notifications\Infrastructure\Models\NotificationDispatch;
use App\Modules\Notifications\Infrastructure\Models\NotificationPreference;
use App\Modules\Notifications\Infrastructure\Models\NotificationToken;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Pharmacies\Domain\Enums\PharmacyDeliveryMethod;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderPaymentStatus;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderStatus;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyOrder;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\DoctorProfile;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Wallets\Domain\Enums\WalletOwnerType;
use App\Modules\Wallets\Domain\Enums\WalletStatus;
use App\Modules\Wallets\Domain\Enums\WithdrawalRequestStatus;
use App\Modules\Wallets\Infrastructure\Models\Wallet;
use App\Modules\Wallets\Infrastructure\Models\WithdrawalRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationsSprint11Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(NotificationTemplateSeeder::class);
        FakeNotificationProvider::$calls = 0;
        FakeNotificationProvider::$lastPayloads = [];
        Carbon::setTestNow(Carbon::parse('2026-05-05 10:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_unauthenticated_notification_routes_are_blocked(): void
    {
        $this->getJson('/api/v1/notifications')->assertUnauthorized();
        $this->postJson('/api/v1/notification-tokens', [])->assertUnauthorized();
        $this->getJson('/api/v1/admin/notifications')->assertUnauthorized();
    }

    public function test_notification_basics_are_owner_scoped(): void
    {
        $user = $this->patientUser();
        $other = $this->patientUser('notify-other@example.com');
        $notification = $this->notification($user);
        $otherNotification = $this->notification($other);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/notifications')->assertOk()->assertJsonFragment(['id' => $notification->id])->assertJsonMissing(['id' => $otherNotification->id]);
        $this->getJson('/api/v1/notifications/unread-count')->assertOk()->assertJsonPath('data.unread_count', 1);
        $this->getJson('/api/v1/notifications/'.$otherNotification->id)->assertForbidden();
        $this->postJson('/api/v1/notifications/'.$notification->id.'/read')->assertOk()->assertJsonPath('data.read_at', fn ($value) => $value !== null);
        $this->getJson('/api/v1/notifications/unread-count')->assertOk()->assertJsonPath('data.unread_count', 0);
        $this->deleteJson('/api/v1/notifications/'.$notification->id)->assertOk();
        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);

        $this->getJson('/api/v1/notifications-public')->assertNotFound();
        $this->postJson('/api/v1/notifications/read-all')->assertOk();
    }

    public function test_tokens_register_update_and_delete_are_owner_scoped_without_fcm_secret(): void
    {
        $user = $this->patientUser();
        $other = $this->patientUser('token-other@example.com');
        Sanctum::actingAs($user);

        $id = $this->postJson('/api/v1/notification-tokens', $this->tokenPayload(['device_name' => 'Phone A']))
            ->assertCreated()
            ->assertJsonPath('data.provider', NotificationTokenProvider::Fcm->value)
            ->assertJsonMissingPath('data.token')
            ->json('data.id');

        $this->postJson('/api/v1/notification-tokens', $this->tokenPayload(['device_name' => 'Phone B']))
            ->assertCreated()
            ->assertJsonPath('data.device_name', 'Phone B');

        $this->assertDatabaseCount('notification_tokens', 1);
        $this->assertDatabaseHas('audit_logs', ['action' => 'notification_token.registered']);

        $otherToken = NotificationToken::query()->create([
            'user_id' => $other->id,
            'token' => 'other-token',
            'token_hash' => hash('sha256', 'other-token'),
            'provider' => NotificationTokenProvider::Fcm,
            'device_type' => NotificationDeviceType::Android,
        ]);

        $this->deleteJson('/api/v1/notification-tokens/'.$otherToken->id)->assertForbidden();
        $this->deleteJson('/api/v1/notification-tokens/'.$id)->assertOk();
    }

    public function test_preferences_defaults_validation_and_dispatch_effects(): void
    {
        $user = $this->patientUser();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/notification-preferences')->assertOk()->assertJsonCount(45, 'data');
        $this->putJson('/api/v1/notification-preferences', [
            'preferences' => [[
                'channel' => NotificationChannel::Push->value,
                'category' => NotificationCategory::System->value,
                'is_enabled' => true,
                'quiet_hours_start' => 'bad',
            ]],
        ])->assertUnprocessable();

        $this->putJson('/api/v1/notification-preferences', [
            'preferences' => [[
                'channel' => NotificationChannel::Push->value,
                'category' => NotificationCategory::System->value,
                'is_enabled' => false,
            ]],
        ])->assertOk();

        $this->activeToken($user);
        app(NotificationService::class)->sendToUser($user, 'system_notice', ['body' => 'Hello'], [
            'category' => NotificationCategory::System,
            'idempotency_key' => 'pref-disabled',
        ]);

        $this->assertDatabaseHas('notification_dispatches', [
            'idempotency_key' => 'pref-disabled:push',
            'status' => NotificationDispatchStatus::Skipped->value,
        ]);
    }

    public function test_admin_template_management_is_protected_and_rejects_secret_variables(): void
    {
        $admin = $this->adminUser();
        $patient = $this->patientUser();
        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/admin/notification-templates', [
            'key' => 'x',
            'category' => NotificationCategory::System->value,
            'channel' => NotificationChannel::InApp->value,
            'title_ar' => 'x',
            'body_ar' => 'x',
        ])->assertForbidden();

        Sanctum::actingAs($admin);
        $this->postJson('/api/v1/admin/notification-templates', [
            'key' => 'unsafe_template',
            'category' => NotificationCategory::System->value,
            'channel' => NotificationChannel::InApp->value,
            'title_ar' => 'Unsafe',
            'body_ar' => 'Unsafe',
            'variables' => ['api_key' => 'secret'],
        ])->assertUnprocessable();

        $id = $this->postJson('/api/v1/admin/notification-templates', [
            'key' => 'safe_template',
            'category' => NotificationCategory::System->value,
            'channel' => NotificationChannel::InApp->value,
            'title_ar' => 'آمن',
            'body_ar' => 'مرحبًا {{name}}',
        ])->assertCreated()->json('data.id');

        $this->putJson('/api/v1/admin/notification-templates/'.$id, ['title_ar' => 'محدث'])->assertOk();
        $this->assertDatabaseHas('audit_logs', ['action' => 'notification_template.updated']);
    }

    public function test_send_to_user_creates_in_app_push_respects_quiet_hours_and_idempotency(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-05 22:30:00', 'Africa/Cairo'));
        $user = $this->patientUser();
        $this->activeToken($user);

        NotificationPreference::query()->create([
            'user_id' => $user->id,
            'channel' => NotificationChannel::Push,
            'category' => NotificationCategory::System,
            'is_enabled' => true,
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '07:00',
            'timezone' => 'Africa/Cairo',
        ]);

        app(NotificationService::class)->sendToUser($user, 'system_notice', ['body' => 'Quiet message'], [
            'category' => NotificationCategory::System,
            'idempotency_key' => 'quiet-key',
        ]);
        app(NotificationService::class)->sendToUser($user, 'system_notice', ['body' => 'Quiet message duplicate'], [
            'category' => NotificationCategory::System,
            'idempotency_key' => 'quiet-key',
        ]);

        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('notification_dispatches', ['idempotency_key' => 'quiet-key:push', 'status' => NotificationDispatchStatus::Skipped->value]);

        app(NotificationService::class)->sendToUser($user, 'system_notice', ['body' => 'Urgent'], [
            'category' => NotificationCategory::System,
            'priority' => NotificationPriority::Urgent,
            'critical' => true,
            'idempotency_key' => 'urgent-key',
        ]);

        $this->assertDatabaseHas('notification_dispatches', ['idempotency_key' => 'urgent-key:push:1', 'status' => NotificationDispatchStatus::Pending->value]);
    }

    public function test_payload_sanitizer_removes_sensitive_values(): void
    {
        $sanitized = app(NotificationPayloadSanitizer::class)->sanitize([
            'file_path' => 'medical_private/results/a.pdf',
            'api_key' => 'secret',
            'provider_net_amount' => 500,
            'commission_amount' => 100,
            'safe_id' => 5,
            'nested' => ['raw_response' => 'unsafe', 'ok' => 'yes'],
        ], NotificationChannel::Push);

        $encoded = json_encode($sanitized);
        $this->assertStringNotContainsString('medical_private', $encoded);
        $this->assertStringNotContainsString('secret', $encoded);
        $this->assertStringNotContainsString('provider_net_amount', $encoded);
        $this->assertStringContainsString('safe_id', $encoded);
    }

    public function test_send_due_dispatch_job_marks_sent_or_failed_and_records_scheduler_run(): void
    {
        $user = $this->patientUser();
        NotificationDispatch::query()->create([
            'user_id' => $user->id,
            'channel' => NotificationChannel::Push,
            'provider' => 'fake',
            'category' => NotificationCategory::System,
            'type' => 'system_notice',
            'recipient' => 'token',
            'title' => 'Safe',
            'body' => 'Safe',
            'payload' => ['safe' => true],
            'status' => NotificationDispatchStatus::Pending,
        ]);
        NotificationDispatch::query()->create([
            'user_id' => $user->id,
            'channel' => NotificationChannel::Push,
            'provider' => 'fcm',
            'category' => NotificationCategory::System,
            'type' => 'system_notice',
            'recipient' => 'token',
            'title' => 'Safe',
            'body' => 'Safe',
            'payload' => ['safe' => true],
            'status' => NotificationDispatchStatus::Pending,
        ]);

        (new SendDueNotificationDispatchesJob)->handle(app(NotificationDispatchService::class), app(SchedulerRunService::class));

        $this->assertSame(1, FakeNotificationProvider::$calls);
        $this->assertDatabaseHas('notification_dispatches', ['provider' => 'fake', 'status' => NotificationDispatchStatus::Sent->value]);
        $this->assertDatabaseHas('notification_dispatches', ['provider' => 'fcm', 'status' => NotificationDispatchStatus::Failed->value]);
        $this->assertDatabaseHas('scheduler_runs', ['status' => 'completed', 'processed_count' => 2, 'failed_count' => 1]);
    }

    public function test_appointment_medication_and_care_plan_jobs_are_idempotent_and_safe(): void
    {
        $patient = $this->patientUser();
        $appointment = $this->appointment($patient);
        $reminder = MedicationReminder::query()->create([
            'patient_user_id' => $patient->id,
            'medication_name' => 'User entered medicine',
            'dosage' => 'user text only',
            'frequency_type' => 'once_daily',
            'start_date' => now()->toDateString(),
            'status' => 'active',
            'source' => 'patient_entered',
        ]);
        MedicationNotificationQueue::query()->create([
            'medication_reminder_id' => $reminder->id,
            'patient_user_id' => $patient->id,
            'scheduled_for' => now(),
            'notification_type' => MedicationNotificationType::MedicationReminder,
            'status' => MedicationNotificationStatus::Pending,
            'channel' => MedicationNotificationChannel::Local,
        ]);
        $plan = CarePlan::query()->create([
            'patient_user_id' => $patient->id,
            'plan_type' => CarePlanType::Nutrition,
            'title' => 'Follow up',
            'start_date' => now()->toDateString(),
            'status' => CarePlanStatus::Active,
            'visibility' => CarePlanVisibility::PatientOnly,
            'source' => CarePlanSource::PatientCreated,
        ]);
        CarePlan::query()->create([
            'patient_user_id' => $patient->id,
            'plan_type' => CarePlanType::Nutrition,
            'title' => 'Completed',
            'start_date' => now()->toDateString(),
            'status' => CarePlanStatus::Completed,
            'visibility' => CarePlanVisibility::PatientOnly,
            'source' => CarePlanSource::PatientCreated,
        ]);

        (new GenerateAppointmentRemindersJob)->handle(app(NotificationSchedulerService::class), app(SchedulerRunService::class));
        (new GenerateAppointmentRemindersJob)->handle(app(NotificationSchedulerService::class), app(SchedulerRunService::class));
        (new GenerateMedicationNotificationsJob)->handle(app(NotificationSchedulerService::class), app(SchedulerRunService::class));
        (new GenerateMedicationNotificationsJob)->handle(app(NotificationSchedulerService::class), app(SchedulerRunService::class));
        (new GenerateCarePlanCheckinRemindersJob)->handle(app(NotificationSchedulerService::class), app(SchedulerRunService::class));
        (new GenerateCarePlanCheckinRemindersJob)->handle(app(NotificationSchedulerService::class), app(SchedulerRunService::class));

        $this->assertDatabaseHas('notification_dispatches', ['idempotency_key' => 'appointment:'.$appointment->id.':reminder:24:in_app']);
        $this->assertDatabaseHas('notification_dispatches', ['idempotency_key' => 'medication:'.$reminder->id.':'.now()->timestamp.':in_app']);
        $this->assertDatabaseHas('notification_dispatches', ['idempotency_key' => 'care_plan:'.$plan->id.':checkin:'.now()->toDateString().':in_app']);
        $this->assertSame(4, Notification::query()->where('user_id', $patient->id)->count());
        $this->assertStringNotContainsString('problem_description', json_encode(NotificationDispatch::query()->pluck('payload')->all()));
        $this->assertStringContainsString('لا تغيّر أي جرعة', Notification::query()->where('type', 'medication_reminder_due')->firstOrFail()->body);
    }

    public function test_operational_integrations_create_expected_notifications_once(): void
    {
        $patient = $this->patientUser();
        $admin = $this->adminUser();
        $labResult = $this->labResult($patient);
        $pharmacyOrder = $this->pharmacyOrder($patient, PharmacyOrderStatus::Rejected);
        $payment = Payment::query()->create([
            'user_id' => $patient->id,
            'amount' => 100,
            'currency' => 'EGP',
            'status' => PaymentStatus::Rejected,
        ]);
        $withdrawal = $this->withdrawal($patient, WithdrawalRequestStatus::Approved);
        $aiEvent = AiSafetyEvent::query()->create([
            'patient_user_id' => $patient->id,
            'event_type' => AiSafetyEventType::RedFlagDetected,
            'severity' => AiSafetySeverity::Critical,
            'description' => 'critical',
            'created_at' => now(),
        ]);

        $integrations = app(NotificationIntegrationService::class);
        $integrations->notifyLabResultReady($labResult);
        $integrations->notifyLabResultReady($labResult);
        $integrations->notifyPharmacyOrderStatus($pharmacyOrder);
        $integrations->notifyPaymentRejected($payment);
        $integrations->notifyWithdrawalStatus($withdrawal);
        (new GenerateAiSafetyAdminAlertsJob)->handle(app(NotificationSchedulerService::class), app(SchedulerRunService::class));

        $this->assertDatabaseHas('notifications', ['user_id' => $patient->id, 'type' => 'lab_result_ready']);
        $this->assertDatabaseHas('notifications', ['user_id' => $patient->id, 'type' => 'pharmacy_order_rejected']);
        $this->assertDatabaseHas('notifications', ['user_id' => $patient->id, 'type' => 'payment_rejected']);
        $this->assertDatabaseHas('notifications', ['user_id' => $patient->id, 'type' => 'withdrawal_approved']);
        $this->assertDatabaseHas('notifications', ['user_id' => $admin->id, 'type' => 'ai_red_flag_admin_alert']);
        $this->assertSame(1, Notification::query()->where('type', 'lab_result_ready')->count());
        $this->assertStringNotContainsString('critical', Notification::query()->where('type', 'ai_red_flag_admin_alert')->firstOrFail()->body);
    }

    public function test_admin_monitoring_routes_are_protected(): void
    {
        $patient = $this->patientUser();
        $admin = $this->adminUser();
        $this->notification($patient);

        Sanctum::actingAs($patient);
        $this->getJson('/api/v1/admin/notifications')->assertForbidden();
        $this->getJson('/api/v1/admin/notification-dispatches')->assertForbidden();

        Sanctum::actingAs($admin);
        $this->getJson('/api/v1/admin/notifications')->assertOk();
        $this->getJson('/api/v1/admin/notification-dispatches')->assertOk();
        $this->getJson('/api/v1/admin/notification-templates')->assertOk();
        $this->getJson('/api/v1/admin/scheduler-runs')->assertOk();
        $this->getJson('/api/v1/admin/notification-tokens')->assertOk();
    }

    private function patientUser(string $email = 'notify-patient@example.com'): User
    {
        $user = User::factory()->create(['email' => $email]);
        $user->assignRole(UserRole::Patient->value);

        return $user;
    }

    private function adminUser(): User
    {
        $user = User::factory()->create(['email' => 'notify-admin-'.Str::random(6).'@example.com']);
        $user->assignRole(UserRole::SuperAdmin->value);

        return $user;
    }

    private function notification(User $user): Notification
    {
        return Notification::query()->create([
            'user_id' => $user->id,
            'category' => NotificationCategory::System,
            'type' => 'system_notice',
            'title' => 'Safe title',
            'body' => 'Safe body',
            'priority' => NotificationPriority::Normal,
        ]);
    }

    private function tokenPayload(array $overrides = []): array
    {
        return [
            'token' => 'fcm-token-123',
            'provider' => NotificationTokenProvider::Fcm->value,
            'device_type' => NotificationDeviceType::Android->value,
            'locale' => 'ar',
            'timezone' => 'Africa/Cairo',
            ...$overrides,
        ];
    }

    private function activeToken(User $user): NotificationToken
    {
        return NotificationToken::query()->create([
            'user_id' => $user->id,
            'token' => 'push-token-'.$user->id,
            'token_hash' => hash('sha256', 'push-token-'.$user->id),
            'provider' => NotificationTokenProvider::Fcm,
            'device_type' => NotificationDeviceType::Android,
            'is_active' => true,
        ]);
    }

    private function provider(ProviderType $type, ?User $owner = null): Provider
    {
        $owner ??= User::factory()->create();

        return Provider::query()->create([
            'type' => $type,
            'owner_user_id' => $owner->id,
            'name_en' => 'Provider '.Str::random(5),
            'status' => ProviderStatus::Approved,
            'is_active' => true,
            'approved_at' => now(),
        ]);
    }

    private function appointment(User $patient): Appointment
    {
        $provider = $this->provider(ProviderType::Doctor);
        $doctor = DoctorProfile::query()->create([
            'provider_id' => $provider->id,
            'user_id' => $provider->owner_user_id,
        ]);
        $slot = AppointmentSlot::query()->create([
            'doctor_profile_id' => $doctor->id,
            'provider_id' => $provider->id,
            'starts_at' => now()->addHour(),
            'ends_at' => now()->addMinutes(90),
            'status' => AppointmentSlotStatus::Booked,
        ]);

        return Appointment::query()->create([
            'appointment_number' => 'APT-NOTIFY-'.Str::upper(Str::random(6)),
            'patient_user_id' => $patient->id,
            'doctor_profile_id' => $doctor->id,
            'provider_id' => $provider->id,
            'appointment_slot_id' => $slot->id,
            'consultation_type' => ConsultationType::Clinic,
            'problem_description' => 'private problem_description',
            'price' => 0,
            'currency' => 'EGP',
            'status' => AppointmentStatus::Confirmed,
            'booked_at' => now(),
        ]);
    }

    private function labResult(User $patient): LabResult
    {
        $provider = $this->provider(ProviderType::Lab);
        $file = UploadedFileModel::query()->create([
            'uploaded_by' => $provider->owner_user_id,
            'disk' => 'medical_private',
            'path' => 'private/labs/result.pdf',
            'original_name' => 'result.pdf',
            'mime_type' => 'application/pdf',
            'size' => 100,
            'file_category' => FileCategory::LabResult,
            'visibility' => FileVisibility::Private,
        ]);
        $order = LabOrder::query()->create([
            'order_number' => 'LAB-NOTIFY-'.Str::upper(Str::random(6)),
            'patient_user_id' => $patient->id,
            'lab_provider_id' => $provider->id,
            'subtotal' => 100,
            'grand_total' => 100,
            'currency' => 'EGP',
            'payment_status' => LabOrderPaymentStatus::Paid,
            'order_status' => LabOrderStatus::ResultReady,
            'sample_collection_method' => LabSampleCollectionMethod::BranchVisit,
        ]);

        return LabResult::query()->create([
            'order_id' => $order->id,
            'uploaded_by' => $provider->owner_user_id,
            'file_id' => $file->id,
            'status' => LabResultStatus::VisibleToPatient,
        ]);
    }

    private function pharmacyOrder(User $patient, PharmacyOrderStatus $status): PharmacyOrder
    {
        $provider = $this->provider(ProviderType::Pharmacy);

        return PharmacyOrder::query()->create([
            'order_number' => 'PH-NOTIFY-'.Str::upper(Str::random(6)),
            'patient_user_id' => $patient->id,
            'pharmacy_provider_id' => $provider->id,
            'subtotal' => 100,
            'grand_total' => 100,
            'currency' => 'EGP',
            'payment_status' => PharmacyOrderPaymentStatus::Unpaid,
            'order_status' => $status,
            'delivery_method' => PharmacyDeliveryMethod::Pickup,
        ]);
    }

    private function withdrawal(User $user, WithdrawalRequestStatus $status): WithdrawalRequest
    {
        $wallet = Wallet::query()->create([
            'owner_type' => WalletOwnerType::Doctor,
            'owner_id' => 1,
            'currency' => 'EGP',
            'status' => WalletStatus::Active,
        ]);

        return WithdrawalRequest::query()->create([
            'wallet_id' => $wallet->id,
            'amount' => 100,
            'status' => $status,
            'requested_by' => $user->id,
        ]);
    }
}
