<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\AdminOperations\Infrastructure\Models\Dispute;
use App\Modules\AdminOperations\Infrastructure\Models\RefundRequest;
use App\Modules\AdminOperations\Infrastructure\Models\SupportTicket;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Domain\Enums\ConsultationType;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\AuditLogs\Infrastructure\Models\AuditLog;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\MedicalFiles\Domain\Enums\FileCategory;
use App\Modules\MedicalFiles\Domain\Enums\FileVisibility;
use App\Modules\MedicalFiles\Infrastructure\Models\UploadedFile;
use App\Modules\Payments\Database\Seeders\PaymentMethodSeeder;
use App\Modules\Payments\Domain\Enums\PaymentMethodType;
use App\Modules\Payments\Domain\Enums\PaymentProofStatus;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;
use App\Modules\Payments\Infrastructure\Models\PaymentProof;
use App\Modules\Providers\Domain\Enums\ApprovalRequestStatus;
use App\Modules\Providers\Domain\Enums\ProviderDocumentStatus;
use App\Modules\Providers\Domain\Enums\ProviderDocumentType;
use App\Modules\Providers\Domain\Enums\ProviderDocumentVisibility;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\DoctorProfile;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderApprovalRequest;
use App\Modules\Providers\Infrastructure\Models\ProviderDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminOperationsSprint58Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(PaymentMethodSeeder::class);
    }

    public function test_non_admin_cannot_access_admin_dashboard_and_admin_can(): void
    {
        Sanctum::actingAs($this->patientUser());
        $this->getJson('/api/v1/admin/operations/dashboard')->assertForbidden();

        Sanctum::actingAs($this->adminUser());
        $this->getJson('/api/v1/admin/operations/dashboard')
            ->assertOk()
            ->assertJsonPath('data.quick_actions.0.key', 'payment_reviews');
    }

    public function test_payment_queue_hides_raw_proof_paths_and_accept_updates_linked_appointment(): void
    {
        [$payment, $appointment] = $this->pendingAppointmentPayment();

        Sanctum::actingAs($this->adminUser());

        $response = $this->getJson('/api/v1/admin/operations/payments/pending')
            ->assertOk();

        $this->assertSame($payment->id, $this->firstCollectionValue($response->json(), 'id'));
        $this->assertTrue((bool) $this->firstCollectionValue($response->json(), 'proof.exists'));
        $this->assertStringNotContainsString('storage/private', $response->getContent());
        $this->assertStringNotContainsString('medical_private', $response->getContent());
        $this->assertStringNotContainsString('private/path/proof.jpg', $response->getContent());

        $this->postJson('/api/v1/admin/operations/payments/'.$payment->id.'/accept')
            ->assertOk()
            ->assertJsonPath('data.status', PaymentStatus::Verified->value);

        $this->assertSame(AppointmentStatus::Confirmed, $appointment->refresh()->status);
        $this->assertTrue(AuditLog::query()->where('action', 'payment.accepted')->exists());
    }

    public function test_payment_reject_requires_reason(): void
    {
        [$payment] = $this->pendingAppointmentPayment();

        Sanctum::actingAs($this->adminUser());
        $this->postJson('/api/v1/admin/operations/payments/'.$payment->id.'/reject')
            ->assertUnprocessable();
    }

    public function test_provider_approval_queue_hides_private_document_paths_and_creates_audit(): void
    {
        [$provider] = $this->pendingProviderWithPrivateDocument();

        Sanctum::actingAs($this->adminUser());

        $response = $this->getJson('/api/v1/admin/operations/providers/pending')
            ->assertOk();

        $this->assertSame($provider->id, $this->firstCollectionValue($response->json(), 'id'));
        $this->assertStringNotContainsString('private/provider-documents/license.pdf', $response->getContent());
        $this->assertStringNotContainsString('storage/private', $response->getContent());

        $this->postJson('/api/v1/admin/operations/providers/'.$provider->id.'/approve', [
            'notes' => 'Approved for local demo.',
        ])->assertOk()
            ->assertJsonPath('data.status', ProviderStatus::Approved->value);

        $this->assertTrue(AuditLog::query()->where('action', 'provider.approved')->exists());
    }

    public function test_support_ticket_scoping_and_internal_notes_are_admin_only(): void
    {
        $patient = $this->patientUser();
        $other = $this->patientUser('other-support@example.test');

        Sanctum::actingAs($patient);
        $ticketId = $this->postJson('/api/v1/support/tickets', [
            'category' => SupportTicket::CATEGORY_PAYMENT,
            'subject' => 'Payment proof help',
            'description' => 'Please review my payment proof.',
        ])->assertCreated()->json('data.id');

        Sanctum::actingAs($other);
        $this->getJson('/api/v1/support/tickets/'.$ticketId)->assertForbidden();

        Sanctum::actingAs($this->adminUser());
        $this->postJson('/api/v1/admin/operations/support/tickets/'.$ticketId.'/internal-note', [
            'message' => 'Internal admin note.',
        ])->assertOk()
            ->assertJsonFragment(['is_internal_note' => true]);

        Sanctum::actingAs($patient);
        $this->getJson('/api/v1/support/tickets/'.$ticketId)
            ->assertOk()
            ->assertJsonMissing(['Internal admin note.']);
    }

    public function test_refund_and_dispute_foundation_work_with_admin_actions(): void
    {
        $patient = $this->patientUser();
        Sanctum::actingAs($patient);

        $refundId = $this->postJson('/api/v1/refunds', [
            'amount' => 120,
            'currency' => 'EGP',
            'reason' => 'Local refund test.',
        ])->assertCreated()
            ->assertJsonPath('data.status', RefundRequest::STATUS_REQUESTED)
            ->json('data.id');

        $disputeId = $this->postJson('/api/v1/disputes', [
            'reason' => 'Local dispute test.',
        ])->assertCreated()
            ->assertJsonPath('data.status', Dispute::STATUS_OPEN)
            ->json('data.id');

        Sanctum::actingAs($this->adminUser());
        $this->postJson('/api/v1/admin/operations/refunds/'.$refundId.'/approve', [
            'notes' => 'Approved manually for local demo.',
        ])->assertOk()
            ->assertJsonPath('data.status', RefundRequest::STATUS_APPROVED);

        $this->postJson('/api/v1/admin/operations/disputes/'.$disputeId.'/resolve', [
            'notes' => 'Resolved manually for local demo.',
        ])->assertOk()
            ->assertJsonPath('data.status', Dispute::STATUS_RESOLVED);

        $this->assertTrue(AuditLog::query()->where('action', 'refund.approved')->exists());
        $this->assertTrue(AuditLog::query()->where('action', 'dispute.resolved')->exists());
    }

    public function test_audit_log_is_admin_only_and_safe(): void
    {
        AuditLog::query()->create([
            'actor_id' => null,
            'action' => 'support.ticket.created',
            'metadata' => ['safe' => 'yes', 'path' => 'storage/private/hidden.jpg'],
        ]);

        Sanctum::actingAs($this->patientUser());
        $this->getJson('/api/v1/admin/operations/audit-log')->assertForbidden();

        Sanctum::actingAs($this->adminUser());
        $this->getJson('/api/v1/admin/operations/audit-log')
            ->assertOk()
            ->assertJsonMissing(['storage/private/hidden.jpg']);
    }

    private function pendingAppointmentPayment(): array
    {
        $patient = $this->patientUser();
        $doctorUser = $this->patientUser('doctor-owner@example.test');
        $provider = Provider::query()->create([
            'type' => ProviderType::Doctor,
            'owner_user_id' => $doctorUser->id,
            'name_ar' => 'طبيب تجريبي',
            'name_en' => 'Demo Doctor',
            'slug' => 'admin-ops-demo-doctor',
            'phone' => '01000000000',
            'email' => $doctorUser->email,
            'status' => ProviderStatus::Approved,
            'is_active' => true,
            'approved_at' => now(),
        ]);
        $doctorProfile = DoctorProfile::query()->create([
            'provider_id' => $provider->id,
            'user_id' => $doctorUser->id,
            'title' => 'Consultant',
            'consultation_fee' => 300,
        ]);

        $appointment = Appointment::query()->create([
            'appointment_number' => 'APT-ADMIN-OPS-1',
            'patient_user_id' => $patient->id,
            'doctor_profile_id' => $doctorProfile->id,
            'provider_id' => $provider->id,
            'consultation_type' => ConsultationType::Clinic,
            'price' => 300,
            'currency' => 'EGP',
            'status' => AppointmentStatus::PendingPaymentReview,
            'booked_at' => now(),
        ]);

        $payment = Payment::query()->create([
            'payable_type' => Appointment::class,
            'payable_id' => $appointment->id,
            'user_id' => $patient->id,
            'provider_id' => $provider->id,
            'provider_type' => 'doctor',
            'payment_method_id' => PaymentMethod::query()->where('type', PaymentMethodType::ManualVodafoneCash)->firstOrFail()->id,
            'amount' => 300,
            'currency' => 'EGP',
            'status' => PaymentStatus::PendingReview,
            'created_by' => $patient->id,
        ]);
        $appointment->update(['payment_id' => $payment->id]);
        $this->attachProof($payment, $patient);

        return [$payment->refresh(), $appointment->refresh()];
    }

    private function pendingProviderWithPrivateDocument(): array
    {
        $owner = $this->patientUser('pending-provider-owner@example.test');
        $provider = Provider::query()->create([
            'type' => ProviderType::Hospital,
            'owner_user_id' => $owner->id,
            'name_ar' => 'مستشفى قيد المراجعة',
            'name_en' => 'Pending Hospital',
            'slug' => 'pending-admin-ops-hospital',
            'phone' => '01000000003',
            'email' => $owner->email,
            'status' => ProviderStatus::PendingReview,
            'is_active' => false,
        ]);

        ProviderApprovalRequest::query()->create([
            'provider_id' => $provider->id,
            'requested_by' => $owner->id,
            'status' => ApprovalRequestStatus::Pending,
            'notes' => 'Pending admin review.',
        ]);

        $file = UploadedFile::query()->create([
            'owner_type' => Provider::class,
            'owner_id' => $provider->id,
            'uploaded_by' => $owner->id,
            'disk' => 'medical_private',
            'path' => 'private/provider-documents/license.pdf',
            'original_name' => 'license.pdf',
            'mime_type' => 'application/pdf',
            'size' => 200,
            'file_category' => FileCategory::ProviderDocument,
            'visibility' => FileVisibility::Private,
        ]);

        ProviderDocument::query()->create([
            'provider_id' => $provider->id,
            'file_id' => $file->id,
            'uploaded_by' => $owner->id,
            'document_type' => ProviderDocumentType::CommercialRegister->value,
            'status' => ProviderDocumentStatus::Pending,
            'visibility' => ProviderDocumentVisibility::AdminOnly,
        ]);

        return [$provider->refresh()];
    }

    private function attachProof(Payment $payment, User $patient): void
    {
        $file = UploadedFile::query()->create([
            'owner_type' => Payment::class,
            'owner_id' => $payment->id,
            'uploaded_by' => $patient->id,
            'disk' => 'medical_private',
            'path' => 'private/path/proof.jpg',
            'original_name' => 'proof.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 100,
            'file_category' => FileCategory::PaymentProof,
            'visibility' => FileVisibility::Private,
        ]);

        PaymentProof::query()->create([
            'payment_id' => $payment->id,
            'uploaded_by' => $patient->id,
            'file_id' => $file->id,
            'status' => PaymentProofStatus::PendingReview,
        ]);
    }

    private function firstCollectionValue(array $payload, string $path): mixed
    {
        foreach ([
            $payload['data'][0] ?? null,
            $payload['data']['data'][0] ?? null,
            $payload['data']['data']['data'][0] ?? null,
        ] as $item) {
            if (! is_array($item)) {
                continue;
            }

            return data_get($item, $path);
        }

        return null;
    }

    private function adminUser(string $email = 'admin-ops@example.test'): User
    {
        $user = User::factory()->create(['email' => $email]);
        $user->assignRole(UserRole::Admin->value);

        return $user;
    }

    private function patientUser(string $email = 'patient-ops@example.test'): User
    {
        $user = User::factory()->create(['email' => $email]);
        $user->assignRole(UserRole::Patient->value);

        return $user;
    }
}
