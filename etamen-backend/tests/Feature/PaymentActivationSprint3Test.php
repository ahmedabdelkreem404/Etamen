<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Appointments\Domain\Enums\AppointmentSlotStatus;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Domain\Enums\ConsultationType;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\Appointments\Infrastructure\Models\AppointmentSlot;
use App\Modules\AuditLogs\Infrastructure\Models\AuditLog;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Payments\Database\Seeders\PaymentMethodSeeder;
use App\Modules\Payments\Domain\Enums\PaymentMethodType;
use App\Modules\Payments\Domain\Enums\PaymentProofStatus;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Infrastructure\Gateways\PaymobGateway;
use App\Modules\Payments\Infrastructure\Models\Invoice;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\DoctorProfile;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentActivationSprint3Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(PaymentMethodSeeder::class);
    }

    public function test_paid_appointment_creates_linked_payment_with_backend_price(): void
    {
        $patient = $this->patientUser();
        $appointment = $this->bookAppointmentThroughApi($patient, fee: 350);
        $payment = $appointment->refresh()->payment;

        $this->assertNotNull($payment);
        $this->assertSame(PaymentStatus::AwaitingMethod, $payment->status);
        $this->assertSame('350.00', $payment->amount);
        $this->assertSame('350.00', $appointment->price);
        $this->assertSame($payment->id, $appointment->payment_id);
    }

    public function test_free_appointment_does_not_require_payment(): void
    {
        $patient = $this->patientUser();
        $appointment = $this->bookAppointmentThroughApi($patient, fee: 0);

        $this->assertSame(AppointmentStatus::Confirmed, $appointment->status);
        $this->assertNull($appointment->payment_id);
        $this->assertSame(0, Payment::query()->count());
    }

    public function test_user_cannot_set_price_from_request(): void
    {
        $patient = $this->patientUser();
        ['doctor' => $doctor] = $this->createDoctorProvider(fee: 300);
        $slot = $this->createSlot($doctor);
        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/appointments', [
            'doctor_profile_id' => $doctor->id,
            'appointment_slot_id' => $slot->id,
            'consultation_type' => ConsultationType::Clinic->value,
            'price' => 1,
        ])->assertUnprocessable();
    }

    public function test_only_allowed_payment_method_types_exist(): void
    {
        $this->assertSame([
            'paymob',
            'manual_vodafone_cash',
            'manual_instapay',
        ], PaymentMethodType::values());

        $this->assertDatabaseMissing('payment_methods', ['type' => 'stripe']);
        $this->assertDatabaseMissing('payment_methods', ['type' => 'paypal']);
        $this->assertDatabaseMissing('payment_methods', ['type' => 'razorpay']);
    }

    public function test_inactive_payment_method_cannot_be_selected(): void
    {
        $patient = $this->patientUser();
        $payment = $this->bookAppointmentThroughApi($patient)->payment;
        $method = $this->paymentMethod(PaymentMethodType::ManualVodafoneCash, active: false);
        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/payments/'.$payment->id.'/manual/select', [
            'payment_method_id' => $method->id,
        ])->assertUnprocessable();
    }

    public function test_patient_can_select_vodafone_cash_and_instapay_manual_methods(): void
    {
        $patient = $this->patientUser();
        $payment = $this->bookAppointmentThroughApi($patient)->payment;
        $vodafone = $this->paymentMethod(PaymentMethodType::ManualVodafoneCash);
        $instapay = $this->paymentMethod(PaymentMethodType::ManualInstapay);
        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/payments/'.$payment->id.'/manual/select', [
            'payment_method_id' => $vodafone->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.payment.status', PaymentStatus::AwaitingProof->value)
            ->assertJsonPath('data.payment.payment_method.type', PaymentMethodType::ManualVodafoneCash->value);

        $this->postJson('/api/v1/payments/'.$payment->id.'/manual/select', [
            'payment_method_id' => $instapay->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.payment.payment_method.type', PaymentMethodType::ManualInstapay->value);
    }

    public function test_patient_can_upload_private_manual_payment_proof_for_own_payment(): void
    {
        Storage::fake('medical_private');

        $patient = $this->patientUser();
        $payment = $this->bookAppointmentThroughApi($patient)->payment;
        $method = $this->paymentMethod(PaymentMethodType::ManualVodafoneCash);
        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/payments/'.$payment->id.'/manual/select', [
            'payment_method_id' => $method->id,
        ])->assertOk();

        $this->post('/api/v1/payments/'.$payment->id.'/proofs', [
            'file' => UploadedFile::fake()->image('proof.jpg'),
            'reference_number' => 'VC-123',
            'sender_phone' => '01012345678',
            'notes' => 'Transferred now.',
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', PaymentStatus::PendingReview->value)
            ->assertJsonMissingPath('data.proofs.0.file.path')
            ->assertJsonMissingPath('data.proofs.0.file.url');

        $payment = $payment->refresh();
        $appointment = Appointment::query()->findOrFail($payment->payable_id);
        $proof = $payment->proofs()->with('file')->firstOrFail();

        $this->assertSame(PaymentStatus::PendingReview, $payment->status);
        $this->assertSame(AppointmentStatus::PendingPaymentReview, $appointment->status);
        $this->assertSame(PaymentProofStatus::PendingReview, $proof->status);
        $this->assertSame('payment_proof', $proof->file->file_category->value);
        $this->assertSame('private', $proof->file->visibility->value);
        $this->assertSame('medical_private', $proof->file->disk);
    }

    public function test_patient_cannot_upload_proof_for_another_users_payment(): void
    {
        $owner = $this->patientUser('owner@example.com');
        $intruder = $this->patientUser('intruder@example.com');
        $payment = $this->bookAppointmentThroughApi($owner)->payment;

        Sanctum::actingAs($intruder);

        $this->post('/api/v1/payments/'.$payment->id.'/proofs', [
            'file' => UploadedFile::fake()->image('proof.jpg'),
        ])->assertForbidden();
    }

    public function test_admin_can_accept_manual_payment_and_generate_invoice_idempotently(): void
    {
        Storage::fake('medical_private');

        $patient = $this->patientUser();
        $payment = $this->prepareManualProof($patient);
        $admin = $this->adminUser();
        Sanctum::actingAs($admin);

        $this->postJson('/api/v1/admin/payments/'.$payment->id.'/accept')
            ->assertOk()
            ->assertJsonPath('data.status', PaymentStatus::Verified->value)
            ->assertJsonPath('data.appointment.status', AppointmentStatus::Confirmed->value);

        $payment = $payment->refresh();
        $appointment = Appointment::query()->findOrFail($payment->payable_id);

        $this->assertSame(PaymentStatus::Verified, $payment->status);
        $this->assertSame(AppointmentStatus::Confirmed, $appointment->status);
        $this->assertSame(1, Invoice::query()->where('payment_id', $payment->id)->count());
        $this->assertSame(PaymentProofStatus::Accepted, $payment->proofs()->firstOrFail()->status);

        $this->postJson('/api/v1/admin/payments/'.$payment->id.'/accept')->assertOk();

        $this->assertSame(1, Invoice::query()->where('payment_id', $payment->id)->count());
    }

    public function test_admin_can_reject_manual_payment_with_reason(): void
    {
        Storage::fake('medical_private');

        $patient = $this->patientUser();
        $payment = $this->prepareManualProof($patient);
        $admin = $this->adminUser();
        Sanctum::actingAs($admin);

        $this->postJson('/api/v1/admin/payments/'.$payment->id.'/reject')
            ->assertUnprocessable();

        $this->postJson('/api/v1/admin/payments/'.$payment->id.'/reject', [
            'reason' => 'Screenshot is unclear.',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', PaymentStatus::Rejected->value)
            ->assertJsonPath('data.appointment.status', AppointmentStatus::PendingPayment->value);

        $this->assertSame(PaymentProofStatus::Rejected, $payment->proofs()->firstOrFail()->refresh()->status);
    }

    public function test_non_admin_cannot_accept_or_reject_payment(): void
    {
        $patient = $this->patientUser();
        $payment = $this->bookAppointmentThroughApi($patient)->payment;

        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/admin/payments/'.$payment->id.'/accept')->assertForbidden();
        $this->postJson('/api/v1/admin/payments/'.$payment->id.'/reject', ['reason' => 'No'])->assertForbidden();
    }

    public function test_create_paymob_session_requires_owner_and_creates_attempt(): void
    {
        $owner = $this->patientUser('paymob-owner@example.com');
        $other = $this->patientUser('paymob-other@example.com');
        $payment = $this->bookAppointmentThroughApi($owner)->payment;
        $this->paymentMethod(PaymentMethodType::Paymob);
        $this->fakePaymobGateway();

        Sanctum::actingAs($other);
        $this->postJson('/api/v1/payments/'.$payment->id.'/paymob/create-session')->assertForbidden();

        Sanctum::actingAs($owner);
        $this->postJson('/api/v1/payments/'.$payment->id.'/paymob/create-session')
            ->assertOk()
            ->assertJsonPath('data.payment.status', PaymentStatus::PendingGateway->value)
            ->assertJsonPath('data.checkout_url', 'https://checkout.paymob.test/session');

        $this->assertSame(1, $payment->attempts()->count());
        $this->assertSame(PaymentStatus::PendingGateway, $payment->refresh()->status);
    }

    public function test_frontend_cannot_mark_paymob_payment_as_verified(): void
    {
        $patient = $this->patientUser();
        $payment = $this->bookAppointmentThroughApi($patient)->payment;
        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/payments/'.$payment->id.'/status', [
            'status' => PaymentStatus::Verified->value,
        ])->assertMethodNotAllowed();

        $this->assertNotSame(PaymentStatus::Verified, $payment->refresh()->status);
    }

    public function test_paymob_callback_requires_valid_hmac(): void
    {
        $payment = $this->pendingPaymobPayment();
        $payload = $this->paymobPayload($payment);

        $this->postJson('/api/v1/payments/paymob/callback', $payload + ['hmac' => 'bad-hmac'])
            ->assertUnprocessable();

        $this->assertNotSame(PaymentStatus::Verified, $payment->refresh()->status);
    }

    public function test_valid_paymob_callback_verifies_payment_and_is_idempotent(): void
    {
        $payment = $this->pendingPaymobPayment();
        $payload = $this->paymobPayload($payment);
        $payload['hmac'] = app(PaymobGateway::class)->calculateHmac($payload);

        $this->postJson('/api/v1/payments/paymob/callback', $payload)
            ->assertOk()
            ->assertJsonPath('data.status', PaymentStatus::Verified->value)
            ->assertJsonPath('data.appointment.status', AppointmentStatus::Confirmed->value);

        $invoiceCount = Invoice::query()->where('payment_id', $payment->id)->count();
        $historyCount = $payment->statusHistories()->count();

        $this->postJson('/api/v1/payments/paymob/callback', $payload)->assertOk();

        $this->assertSame($invoiceCount, Invoice::query()->where('payment_id', $payment->id)->count());
        $this->assertSame($historyCount, $payment->statusHistories()->count());
    }

    public function test_failed_paymob_callback_does_not_confirm_appointment(): void
    {
        $payment = $this->pendingPaymobPayment();
        $payload = $this->paymobPayload($payment, success: false);
        $payload['hmac'] = app(PaymobGateway::class)->calculateHmac($payload);

        $this->postJson('/api/v1/payments/paymob/webhook', $payload)
            ->assertOk()
            ->assertJsonPath('data.status', PaymentStatus::Failed->value)
            ->assertJsonPath('data.appointment.status', AppointmentStatus::PendingPayment->value);

        $this->assertSame(0, Invoice::query()->where('payment_id', $payment->id)->count());
    }

    public function test_payment_status_visibility_and_safe_response(): void
    {
        $owner = $this->patientUser('status-owner@example.com');
        $other = $this->patientUser('status-other@example.com');
        $payment = $this->bookAppointmentThroughApi($owner)->payment;

        Sanctum::actingAs($owner);
        $this->getJson('/api/v1/payments/'.$payment->id.'/status')
            ->assertOk()
            ->assertJsonPath('data.id', $payment->id)
            ->assertJsonMissingPath('data.attempts')
            ->assertJsonMissingPath('data.request_payload')
            ->assertJsonMissingPath('data.response_payload');

        $this->assertStringNotContainsString('PAYMOB_SECRET_KEY', $this->getJson('/api/v1/payments/'.$payment->id.'/status')->getContent());

        Sanctum::actingAs($other);
        $this->getJson('/api/v1/payments/'.$payment->id.'/status')->assertForbidden();
    }

    public function test_verified_payment_creates_histories_and_audit_logs(): void
    {
        Storage::fake('medical_private');

        $patient = $this->patientUser();
        $payment = $this->prepareManualProof($patient);
        $admin = $this->adminUser();
        Sanctum::actingAs($admin);

        $this->postJson('/api/v1/admin/payments/'.$payment->id.'/accept')->assertOk();

        $appointment = Appointment::query()->findOrFail($payment->payable_id);

        $this->assertTrue($payment->refresh()->statusHistories()->where('to_status', PaymentStatus::Verified->value)->exists());
        $this->assertTrue($appointment->statusHistories()->where('to_status', AppointmentStatus::Confirmed->value)->exists());
        $this->assertDatabaseHas('audit_logs', ['action' => 'payment.verified']);
        $this->assertGreaterThan(0, AuditLog::query()->where('action', 'payment.manual_proof_accepted')->count());
    }

    private function prepareManualProof(User $patient): Payment
    {
        $payment = $this->bookAppointmentThroughApi($patient)->payment;
        $method = $this->paymentMethod(PaymentMethodType::ManualVodafoneCash);
        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/payments/'.$payment->id.'/manual/select', [
            'payment_method_id' => $method->id,
        ])->assertOk();

        $this->post('/api/v1/payments/'.$payment->id.'/proofs', [
            'file' => UploadedFile::fake()->image('proof.jpg'),
            'reference_number' => 'VC-123',
            'sender_phone' => '01012345678',
        ])->assertCreated();

        return $payment->refresh();
    }

    private function pendingPaymobPayment(): Payment
    {
        $patient = $this->patientUser('paymob-callback@example.com');
        $payment = $this->bookAppointmentThroughApi($patient)->payment;
        $method = $this->paymentMethod(PaymentMethodType::Paymob);

        $payment->update([
            'payment_method_id' => $method->id,
            'status' => PaymentStatus::PendingGateway,
        ]);

        $payment->attempts()->create([
            'method_type' => PaymentMethodType::Paymob,
            'gateway_reference' => 'paymob-txn-'.$payment->id,
            'status' => 'created',
        ]);

        Config::set('paymob.hmac_secret', 'test-hmac-secret');

        return $payment->refresh();
    }

    private function paymobPayload(Payment $payment, bool $success = true): array
    {
        return [
            'amount_cents' => (string) ((int) round((float) $payment->amount * 100)),
            'created_at' => '2026-05-05T12:00:00',
            'currency' => 'EGP',
            'error_occured' => $success ? 'false' : 'true',
            'has_parent_transaction' => 'false',
            'id' => 'paymob-txn-'.$payment->id,
            'integration_id' => '123456',
            'is_3d_secure' => 'true',
            'is_auth' => 'false',
            'is_capture' => 'false',
            'is_refunded' => 'false',
            'is_standalone_payment' => 'true',
            'is_voided' => 'false',
            'order' => [
                'id' => 'order-'.$payment->id,
                'merchant_order_id' => 'ETAMEN-PAY-'.$payment->id,
            ],
            'owner' => '1',
            'pending' => 'false',
            'source_data' => [
                'pan' => '2346',
                'sub_type' => 'MasterCard',
                'type' => 'card',
            ],
            'success' => $success ? 'true' : 'false',
        ];
    }

    private function fakePaymobGateway(): void
    {
        $this->app->instance(PaymobGateway::class, new class extends PaymobGateway
        {
            public function createSession(array $payload): array
            {
                return [
                    'gateway_reference' => 'paymob-session-'.$payload['payment_id'],
                    'client_secret' => 'client-secret',
                    'checkout_url' => 'https://checkout.paymob.test/session',
                    'request_payload' => $payload,
                    'response_payload' => ['id' => 'paymob-session-'.$payload['payment_id']],
                ];
            }
        });
    }

    private function paymentMethod(PaymentMethodType $type, bool $active = true): PaymentMethod
    {
        $method = PaymentMethod::query()->where('type', $type)->firstOrFail();
        $method->update(['is_active' => $active]);

        return $method->refresh();
    }

    private function bookAppointmentThroughApi(User $patient, int|float $fee = 300): Appointment
    {
        ['doctor' => $doctor] = $this->createDoctorProvider(fee: $fee);
        $slot = $this->createSlot($doctor);
        Sanctum::actingAs($patient);

        $appointmentId = $this->postJson('/api/v1/appointments', [
            'doctor_profile_id' => $doctor->id,
            'appointment_slot_id' => $slot->id,
            'consultation_type' => ConsultationType::Clinic->value,
            'problem_description' => 'Need consultation.',
        ])
            ->assertCreated()
            ->json('data.id');

        return Appointment::query()->with('payment')->findOrFail($appointmentId);
    }

    private function patientUser(string $email = 'patient@example.com'): User
    {
        $user = User::factory()->create(['email' => $email]);
        $user->assignRole(UserRole::Patient->value);

        return $user;
    }

    private function adminUser(): User
    {
        $user = User::factory()->create(['email' => 'admin-'.Str::random(6).'@example.com']);
        $user->assignRole(UserRole::SuperAdmin->value);

        return $user;
    }

    private function createDoctorProvider(int|float $fee = 300): array
    {
        $user = User::factory()->create(['email' => 'doctor-'.Str::random(8).'@example.com']);
        $user->assignRole(UserRole::Doctor->value);

        $provider = Provider::query()->create([
            'type' => ProviderType::Doctor,
            'owner_user_id' => $user->id,
            'name_en' => 'Doctor Provider '.Str::random(6),
            'status' => ProviderStatus::Approved,
            'is_active' => true,
            'approved_at' => now(),
            'created_by' => $user->id,
        ]);

        $provider->staff()->create([
            'user_id' => $user->id,
            'role' => ProviderStaffRole::Owner,
            'is_owner' => true,
            'status' => 'active',
        ]);

        $doctor = DoctorProfile::query()->create([
            'provider_id' => $provider->id,
            'user_id' => $user->id,
            'consultation_fee' => $fee,
        ]);

        return ['provider' => $provider, 'doctor' => $doctor, 'user' => $user];
    }

    private function createSlot(DoctorProfile $doctor): AppointmentSlot
    {
        $startsAt = now()->addDays(3)->setTime(9, 0);

        return AppointmentSlot::query()->create([
            'doctor_profile_id' => $doctor->id,
            'provider_id' => $doctor->provider_id,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addMinutes(30),
            'status' => AppointmentSlotStatus::Available,
        ]);
    }
}
