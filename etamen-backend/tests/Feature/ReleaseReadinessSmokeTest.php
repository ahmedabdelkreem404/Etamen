<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\AI\Domain\Enums\AiProvider;
use App\Modules\AI\Infrastructure\Providers\FakeAiProvider;
use App\Modules\Appointments\Domain\Enums\AppointmentSlotStatus;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Domain\Enums\ConsultationType;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\Appointments\Infrastructure\Models\AppointmentSlot;
use App\Modules\Health\Domain\Enums\Gender;
use App\Modules\Health\Domain\Enums\VitalType;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Labs\Domain\Enums\LabOrderItemType;
use App\Modules\Labs\Domain\Enums\LabOrderStatus;
use App\Modules\Labs\Domain\Enums\LabSampleCollectionMethod;
use App\Modules\Labs\Infrastructure\Models\LabOrder;
use App\Modules\Labs\Infrastructure\Models\LabTest;
use App\Modules\MedicalFiles\Domain\Enums\FileCategory;
use App\Modules\Medications\Domain\Enums\MedicationFrequencyType;
use App\Modules\Notifications\Application\Jobs\SendDueNotificationDispatchesJob;
use App\Modules\Notifications\Application\Providers\FakeNotificationProvider;
use App\Modules\Notifications\Application\Services\NotificationDispatchService;
use App\Modules\Notifications\Application\Services\NotificationService;
use App\Modules\Notifications\Application\Services\SchedulerRunService;
use App\Modules\Notifications\Database\Seeders\NotificationTemplateSeeder;
use App\Modules\Notifications\Domain\Enums\NotificationCategory;
use App\Modules\Notifications\Domain\Enums\NotificationChannel;
use App\Modules\Notifications\Domain\Enums\NotificationDeviceType;
use App\Modules\Notifications\Domain\Enums\NotificationDispatchStatus;
use App\Modules\Notifications\Domain\Enums\NotificationTokenProvider;
use App\Modules\Notifications\Infrastructure\Models\NotificationDispatch;
use App\Modules\Payments\Database\Seeders\PaymentMethodSeeder;
use App\Modules\Payments\Domain\Enums\PaymentMethodType;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;
use App\Modules\Pharmacies\Domain\Enums\PharmacyDeliveryMethod;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderStatus;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyOrder;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyProduct;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Domain\Enums\ServiceType;
use App\Modules\Providers\Infrastructure\Models\DoctorProfile;
use App\Modules\Providers\Infrastructure\Models\LabProfile;
use App\Modules\Providers\Infrastructure\Models\PharmacyProfile;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderStaff;
use App\Modules\Wallets\Domain\Enums\WalletTransactionType;
use App\Modules\Wallets\Infrastructure\Models\CommissionRule;
use App\Modules\Wallets\Infrastructure\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReleaseReadinessSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(PaymentMethodSeeder::class);
        $this->seed(NotificationTemplateSeeder::class);
        PaymentMethod::query()->update(['is_active' => true]);
        config(['ai.default_provider' => AiProvider::Fake->value]);
        FakeAiProvider::$calls = 0;
        FakeAiProvider::$nextResponseContent = null;
        FakeNotificationProvider::$calls = 0;
        Storage::fake('medical_private');
    }

    public function test_doctor_paid_appointment_smoke_flow_releases_wallet_earning(): void
    {
        $patient = $this->registerAndLoginPatient();
        $admin = $this->adminUser();
        ['user' => $doctorUser, 'provider' => $provider, 'doctor' => $doctor] = $this->doctorProvider(300);
        $this->commission(ProviderType::Doctor, ServiceType::Appointment);
        $slot = AppointmentSlot::query()->create([
            'doctor_profile_id' => $doctor->id,
            'provider_id' => $provider->id,
            'starts_at' => now()->addDay()->setTime(10, 0),
            'ends_at' => now()->addDay()->setTime(10, 30),
            'status' => AppointmentSlotStatus::Available,
        ]);

        $appointmentId = $this->withToken($patient['token'])->postJson('/api/v1/appointments', [
            'doctor_profile_id' => $doctor->id,
            'appointment_slot_id' => $slot->id,
            'consultation_type' => ConsultationType::Clinic->value,
            'problem_description' => 'Private description.',
        ])->assertCreated()->assertJsonPath('data.status', AppointmentStatus::PendingPayment->value)->json('data.id');

        $appointment = Appointment::query()->findOrFail($appointmentId);
        $this->verifyManualPayment($patient['user'], $admin, $appointment->payment);
        $this->assertSame(AppointmentStatus::Confirmed, $appointment->refresh()->status);

        Sanctum::actingAs($doctorUser);
        $this->postJson('/api/v1/provider/appointments/'.$appointment->id.'/accept')->assertOk();
        $this->postJson('/api/v1/provider/appointments/'.$appointment->id.'/complete')
            ->assertOk()
            ->assertJsonPath('data.status', AppointmentStatus::Completed->value);

        $this->assertDatabaseHas('wallet_transactions', [
            'idempotency_key' => 'payment:'.$appointment->payment_id.':provider_hold',
            'type' => WalletTransactionType::Hold->value,
        ]);
        $this->assertDatabaseHas('wallet_transactions', [
            'idempotency_key' => 'appointment:'.$appointment->id.':provider_release',
            'type' => WalletTransactionType::Release->value,
        ]);
    }

    public function test_pharmacy_and_lab_paid_order_smoke_flows_release_wallet_earnings_and_keep_files_private(): void
    {
        $patient = $this->patientUser();
        $admin = $this->adminUser();
        ['user' => $pharmacyUser, 'provider' => $pharmacy] = $this->pharmacyProvider();
        ['user' => $labUser, 'provider' => $lab] = $this->labProvider();
        $this->commission(ProviderType::Pharmacy, ServiceType::PharmacyOrder);
        $this->commission(ProviderType::Lab, ServiceType::LabOrder);

        $product = PharmacyProduct::query()->create([
            'provider_id' => $pharmacy->id,
            'name_en' => 'Safe product',
            'price' => 100,
            'stock_quantity' => 5,
            'is_active' => true,
            'requires_prescription' => false,
        ]);
        $labTest = LabTest::query()->create([
            'provider_id' => $lab->id,
            'name_en' => 'CBC',
            'price' => 150,
            'is_active' => true,
        ]);

        Sanctum::actingAs($patient);
        $pharmacyOrderId = $this->postJson('/api/v1/pharmacy/orders', [
            'pharmacy_provider_id' => $pharmacy->id,
            'delivery_method' => PharmacyDeliveryMethod::Pickup->value,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertCreated()->json('data.id');

        Sanctum::actingAs($pharmacyUser);
        $this->patchJson('/api/v1/provider/pharmacy/orders/'.$pharmacyOrderId.'/status', [
            'status' => PharmacyOrderStatus::Accepted->value,
        ])->assertOk();

        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/pharmacy/orders/'.$pharmacyOrderId.'/pay')->assertOk();
        $pharmacyOrder = PharmacyOrder::query()->findOrFail($pharmacyOrderId);
        $this->verifyManualPayment($patient, $admin, $pharmacyOrder->payment);

        Sanctum::actingAs($pharmacyUser);
        $this->patchJson('/api/v1/provider/pharmacy/orders/'.$pharmacyOrderId.'/status', [
            'status' => PharmacyOrderStatus::Delivered->value,
        ])->assertOk();

        $this->assertSame(1, WalletTransaction::query()->where('idempotency_key', 'pharmacy_order:'.$pharmacyOrderId.':provider_release')->count());

        Sanctum::actingAs($patient);
        $labOrderId = $this->postJson('/api/v1/lab/orders', [
            'lab_provider_id' => $lab->id,
            'sample_collection_method' => LabSampleCollectionMethod::BranchVisit->value,
            'items' => [[
                'item_type' => LabOrderItemType::Test->value,
                'test_id' => $labTest->id,
                'quantity' => 1,
            ]],
        ])->assertCreated()->json('data.id');

        Sanctum::actingAs($labUser);
        $this->patchJson('/api/v1/provider/lab/orders/'.$labOrderId.'/status', [
            'status' => LabOrderStatus::Accepted->value,
        ])->assertOk();

        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/lab/orders/'.$labOrderId.'/pay')->assertOk();
        $labOrder = LabOrder::query()->findOrFail($labOrderId);
        $this->verifyManualPayment($patient, $admin, $labOrder->payment);

        Sanctum::actingAs($labUser);
        $resultId = $this->post('/api/v1/provider/lab/orders/'.$labOrderId.'/results', [
            'file' => UploadedFile::fake()->create('result.pdf', 50, 'application/pdf'),
            'title_en' => 'Result',
        ])->assertCreated()->assertJsonMissingPath('data.file.path')->json('data.id');

        Sanctum::actingAs($patient);
        $this->get('/api/v1/lab/results/'.$resultId.'/download')->assertOk();

        Sanctum::actingAs($labUser);
        $this->patchJson('/api/v1/provider/lab/orders/'.$labOrderId.'/status', [
            'status' => LabOrderStatus::Completed->value,
        ])->assertOk();

        $this->assertDatabaseHas('uploaded_files', [
            'file_category' => FileCategory::LabResult->value,
            'disk' => 'medical_private',
            'visibility' => 'private',
        ]);
        $this->assertSame(1, WalletTransaction::query()->where('idempotency_key', 'lab_order:'.$labOrderId.':provider_release')->count());
    }

    public function test_health_medication_care_plan_ai_and_notification_smoke_flow(): void
    {
        $patient = $this->patientUser();
        Sanctum::actingAs($patient);

        $this->putJson('/api/v1/health/profile', [
            'gender' => Gender::Male->value,
            'height_cm' => 175,
            'weight_kg' => 80,
        ])->assertOk();

        $this->postJson('/api/v1/health/vitals', [
            'vital_type' => VitalType::BloodPressure->value,
            'measured_at' => now()->toDateTimeString(),
            'value_decimal' => 130,
            'value_secondary_decimal' => 85,
        ])->assertCreated()->assertJsonPath('success', true);

        $reminderId = $this->postJson('/api/v1/medications/reminders', [
            'medication_name' => 'User entered medicine',
            'frequency_type' => MedicationFrequencyType::OnceDaily->value,
            'start_date' => now()->toDateString(),
            'times' => [['time_of_day' => '09:00']],
        ])->assertCreated()->json('data.id');

        $this->postJson('/api/v1/medications/reminders/'.$reminderId.'/taken', [
            'scheduled_for' => now()->setTime(9, 0)->toDateTimeString(),
        ])->assertOk();

        $planId = $this->postJson('/api/v1/care-plans', [
            'plan_type' => 'nutrition',
            'title' => 'Follow up plan',
            'start_date' => now()->toDateString(),
        ])->assertCreated()->json('data.id');
        $this->postJson('/api/v1/care-plans/'.$planId.'/activate')->assertOk();
        $this->postJson('/api/v1/care-plans/'.$planId.'/checkins', [
            'checkin_date' => now()->toDateString(),
            'commitment_score' => 80,
        ])->assertCreated();

        $conversationId = $this->postJson('/api/v1/ai/conversations', ['language' => 'ar'])->assertCreated()->json('data.id');
        $this->postJson('/api/v1/ai/conversations/'.$conversationId.'/messages', [
            'content' => 'شخصني من الأعراض دي',
        ])->assertCreated()->assertJsonPath('data.was_refused', true);
        $this->postJson('/api/v1/ai/conversations/'.$conversationId.'/messages', [
            'content' => 'ساعدني أجهز أسئلة للدكتور',
        ])->assertCreated();
        $this->assertSame(1, FakeAiProvider::$calls);

        $this->postJson('/api/v1/notification-tokens', [
            'token' => 'release-fake-token',
            'provider' => NotificationTokenProvider::Fcm->value,
            'device_type' => NotificationDeviceType::Android->value,
        ])->assertCreated();

        app(NotificationService::class)->sendToUser($patient, 'system_notice', ['body' => 'Safe notice'], [
            'category' => NotificationCategory::System,
            'idempotency_key' => 'release-smoke-notification',
        ]);
        NotificationDispatch::query()->create([
            'user_id' => $patient->id,
            'channel' => NotificationChannel::Push,
            'provider' => 'fake',
            'category' => NotificationCategory::System,
            'type' => 'system_notice',
            'recipient' => 'fake-token',
            'title' => 'Safe',
            'body' => 'Safe',
            'payload' => ['safe' => true],
            'status' => NotificationDispatchStatus::Pending,
        ]);
        $this->getJson('/api/v1/notifications/unread-count')->assertOk()->assertJsonPath('data.unread_count', 1);

        (new SendDueNotificationDispatchesJob)->handle(app(NotificationDispatchService::class), app(SchedulerRunService::class));
        $this->assertSame(1, FakeNotificationProvider::$calls);
    }

    private function registerAndLoginPatient(): array
    {
        $email = 'release-login@example.com';
        $password = 'Password123';

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Release Patient',
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ])->assertCreated();

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $email,
            'password' => $password,
        ])->assertOk()->json('data.token');

        return [
            'user' => User::query()->where('email', $email)->firstOrFail(),
            'token' => $token,
        ];
    }

    private function verifyManualPayment(User $patient, User $admin, Payment $payment): void
    {
        $method = PaymentMethod::query()->where('type', PaymentMethodType::ManualVodafoneCash)->firstOrFail();

        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/payments/'.$payment->id.'/manual/select', [
            'payment_method_id' => $method->id,
        ])->assertOk();
        $this->post('/api/v1/payments/'.$payment->id.'/proofs', [
            'file' => UploadedFile::fake()->image('proof.jpg'),
            'reference_number' => 'REF-'.Str::upper(Str::random(6)),
        ])->assertCreated();

        Sanctum::actingAs($admin);
        $this->postJson('/api/v1/admin/payments/'.$payment->id.'/accept')->assertOk();
    }

    private function patientUser(string $email = 'release-smoke-patient@example.com'): User
    {
        $user = User::factory()->create(['email' => $email]);
        $user->assignRole(UserRole::Patient->value);

        return $user;
    }

    private function adminUser(): User
    {
        $user = User::factory()->create(['email' => 'release-smoke-admin-'.Str::random(6).'@example.com']);
        $user->assignRole(UserRole::SuperAdmin->value);

        return $user;
    }

    private function doctorProvider(float $fee): array
    {
        $user = User::factory()->create(['email' => 'release-doctor-'.Str::random(6).'@example.com']);
        $user->assignRole(UserRole::Doctor->value);
        $provider = $this->provider($user, ProviderType::Doctor);
        $doctor = DoctorProfile::query()->create([
            'provider_id' => $provider->id,
            'user_id' => $user->id,
            'consultation_fee' => $fee,
        ]);

        return ['user' => $user, 'provider' => $provider, 'doctor' => $doctor];
    }

    private function pharmacyProvider(): array
    {
        $user = User::factory()->create(['email' => 'release-pharmacy-'.Str::random(6).'@example.com']);
        $user->assignRole(UserRole::PharmacyAdmin->value);
        $provider = $this->provider($user, ProviderType::Pharmacy);
        PharmacyProfile::query()->create(['provider_id' => $provider->id]);

        return ['user' => $user, 'provider' => $provider];
    }

    private function labProvider(): array
    {
        $user = User::factory()->create(['email' => 'release-lab-'.Str::random(6).'@example.com']);
        $user->assignRole(UserRole::LabAdmin->value);
        $provider = $this->provider($user, ProviderType::Lab);
        LabProfile::query()->create(['provider_id' => $provider->id]);

        return ['user' => $user, 'provider' => $provider];
    }

    private function provider(User $owner, ProviderType $type): Provider
    {
        $provider = Provider::query()->create([
            'type' => $type,
            'owner_user_id' => $owner->id,
            'name_en' => 'Release '.$type->value.' '.Str::random(4),
            'status' => ProviderStatus::Approved,
            'is_active' => true,
            'approved_at' => now(),
        ]);

        ProviderStaff::query()->create([
            'provider_id' => $provider->id,
            'user_id' => $owner->id,
            'role' => ProviderStaffRole::Owner,
            'is_owner' => true,
            'status' => 'active',
        ]);

        return $provider;
    }

    private function commission(ProviderType $providerType, ServiceType $serviceType): void
    {
        CommissionRule::query()->create([
            'provider_type' => $providerType,
            'service_type' => $serviceType,
            'percentage' => 10,
            'fixed_amount' => 0,
            'starts_at' => now()->subDay(),
            'is_active' => true,
        ]);
    }
}
