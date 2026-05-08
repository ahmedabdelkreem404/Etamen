<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\AuditLogs\Infrastructure\Models\AuditLog;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\MedicalFiles\Domain\Enums\FileCategory;
use App\Modules\MedicalFiles\Domain\Enums\FileVisibility;
use App\Modules\MedicalFiles\Infrastructure\Models\UploadedFile as UploadedFileModel;
use App\Modules\Providers\Domain\Enums\ProviderContractStatus;
use App\Modules\Providers\Domain\Enums\ProviderContractType;
use App\Modules\Providers\Domain\Enums\ProviderDocumentStatus;
use App\Modules\Providers\Domain\Enums\ProviderDocumentType;
use App\Modules\Providers\Domain\Enums\ProviderDocumentVisibility;
use App\Modules\Providers\Domain\Enums\ProviderSettlementCycle;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\DoctorProfile;
use App\Modules\Providers\Infrastructure\Models\HospitalDepartment;
use App\Modules\Providers\Infrastructure\Models\HospitalDoctor;
use App\Modules\Providers\Infrastructure\Models\HospitalProfile;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderBookingSetting;
use App\Modules\Providers\Infrastructure\Models\ProviderContract;
use App\Modules\Providers\Infrastructure\Models\ProviderDocument;
use App\Modules\Providers\Infrastructure\Models\ProviderService;
use App\Modules\Providers\Infrastructure\Models\RadiologyProfile;
use App\Modules\Providers\Infrastructure\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UnifiedProviderFoundationSprint36Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_provider_types_expand_without_breaking_existing_public_discovery(): void
    {
        $this->assertContains(ProviderType::Hospital->value, ProviderType::values());
        $this->assertContains(ProviderType::Radiology->value, ProviderType::values());
        $this->assertContains(ProviderType::Gym->value, ProviderType::values());
        $this->assertContains(ProviderType::FitnessCoach->value, ProviderType::values());
        $this->assertContains(ProviderType::HomeHealthcare->value, ProviderType::values());

        $doctor = $this->createProvider(ProviderType::Doctor, ProviderStatus::Approved, true);
        $this->createProvider(ProviderType::Hospital, ProviderStatus::Approved, true);

        $this->getJson('/api/v1/doctors')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $doctor->id);
    }

    public function test_generic_provider_onboarding_creates_internal_future_profile_and_keeps_it_non_public(): void
    {
        $response = $this->postJson('/api/v1/providers/register', [
            'name' => 'Radiology Owner',
            'email' => 'radiology-owner@example.test',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'provider_type' => ProviderType::Radiology->value,
            'provider_name_en' => 'Future Radiology Center',
            'provider_name_ar' => 'مركز أشعة تجريبي',
            'branch_name_en' => 'Main Radiology Branch',
            'address_line_1' => '12 Test Street',
            'latitude' => 30.0444,
            'longitude' => 31.2357,
            'profile' => [
                'license_number' => 'RAD-123',
                'dicom_supported' => true,
            ],
        ])->assertCreated();

        $providerId = $response->json('data.provider.id');
        $owner = User::query()->where('email', 'radiology-owner@example.test')->firstOrFail();

        $this->assertTrue($owner->hasRole(UserRole::ProviderAdmin->value));
        $this->assertTrue(RadiologyProfile::query()->where('provider_id', $providerId)->where('dicom_supported', true)->exists());
        $this->assertDatabaseHas('provider_branches', [
            'provider_id' => $providerId,
            'address_line_1' => '12 Test Street',
            'latitude' => 30.0444,
            'longitude' => 31.2357,
        ]);
        $this->assertDatabaseMissing('providers', [
            'id' => $providerId,
            'status' => ProviderStatus::Approved->value,
        ]);
    }

    public function test_branch_location_fields_are_safe_public_summary_for_approved_providers_only(): void
    {
        $approved = $this->createProvider(ProviderType::Doctor, ProviderStatus::Approved, true);
        $pending = $this->createProvider(ProviderType::Doctor, ProviderStatus::PendingReview, false);

        $approved->branches()->create([
            'name_en' => 'Map Branch',
            'address_line_1' => 'Tower A',
            'district' => 'Nasr City',
            'latitude' => 30.05,
            'longitude' => 31.33,
            'is_main' => true,
            'is_active' => true,
        ]);
        $pending->branches()->create([
            'name_en' => 'Hidden Branch',
            'address_line_1' => 'Private pending address',
            'latitude' => 1,
            'longitude' => 2,
            'is_main' => true,
            'is_active' => true,
        ]);

        $this->getJson('/api/v1/doctors')
            ->assertOk()
            ->assertJsonPath('data.0.branches.0.address_line_1', 'Tower A')
            ->assertJsonPath('data.0.branches.0.district', 'Nasr City')
            ->assertJsonPath('data.0.branches.0.latitude', '30.0500000')
            ->assertJsonMissing(['Private pending address']);
    }

    public function test_provider_document_visibility_blocks_private_documents_and_allows_safe_public_certificate_metadata(): void
    {
        $admin = $this->adminUser();
        $provider = $this->createProvider(ProviderType::Doctor, ProviderStatus::Approved, true);
        $nationalId = $this->createProviderDocument($provider, ProviderDocumentType::NationalId, ProviderDocumentVisibility::PublicCertificate);
        $certificate = $this->createProviderDocument($provider, ProviderDocumentType::Certificate, ProviderDocumentVisibility::PublicCertificate);
        $rejected = $this->createProviderDocument($provider, ProviderDocumentType::CoachCertificate, ProviderDocumentVisibility::PublicCertificate);

        $this->assertSame(ProviderDocumentVisibility::AdminOnly, $nationalId->refresh()->visibility);

        Sanctum::actingAs($admin);
        $this->postJson('/api/v1/admin/provider-documents/'.$certificate->id.'/approve', [
            'visibility' => ProviderDocumentVisibility::PublicCertificate->value,
        ])->assertOk();
        $this->postJson('/api/v1/admin/provider-documents/'.$rejected->id.'/reject', [
            'notes' => 'Not readable',
        ])->assertOk();

        $this->getJson('/api/v1/doctors')
            ->assertOk()
            ->assertJsonCount(1, 'data.0.public_certificates')
            ->assertJsonPath('data.0.public_certificates.0.document_type', ProviderDocumentType::Certificate->value)
            ->assertJsonMissing(['private/national-id.pdf'])
            ->assertJsonMissing(['private/rejected.pdf'])
            ->assertJsonMissingPath('data.0.public_certificates.0.file.path');

        $this->assertTrue(AuditLog::query()->where('action', 'provider_document.approved')->exists());
        $this->assertTrue(AuditLog::query()->where('action', 'provider_document.rejected')->exists());
    }

    public function test_provider_onboarding_review_needs_changes_and_owner_cannot_force_approval(): void
    {
        $admin = $this->adminUser();
        $provider = $this->createProvider(ProviderType::Clinic, ProviderStatus::PendingReview, false);

        Sanctum::actingAs($admin);
        $this->postJson('/api/v1/admin/providers/'.$provider->id.'/request-changes', [
            'notes' => 'Missing facility license',
        ])->assertOk()
            ->assertJsonPath('data.status', ProviderStatus::NeedsChanges->value);

        Sanctum::actingAs($provider->owner);
        $this->putJson('/api/v1/provider/profile', [
            'status' => ProviderStatus::Approved->value,
            'is_active' => true,
            'name_en' => 'Forced Approval Attempt',
        ])->assertUnprocessable();

        $provider->refresh();
        $this->assertSame(ProviderStatus::NeedsChanges, $provider->status);
        $this->assertFalse($provider->is_active);
    }

    public function test_hospital_departments_and_doctor_links_validate_provider_types_and_public_visibility(): void
    {
        $hospital = $this->createProvider(ProviderType::Hospital, ProviderStatus::Approved, true);
        $doctor = $this->createProvider(ProviderType::Doctor, ProviderStatus::Approved, true);
        $pendingDoctor = $this->createProvider(ProviderType::Doctor, ProviderStatus::PendingReview, false);

        $department = HospitalDepartment::query()->create([
            'hospital_provider_id' => $hospital->id,
            'name_ar' => 'القلب',
            'name_en' => 'Cardiology',
            'is_active' => true,
        ]);

        HospitalDoctor::query()->create([
            'hospital_provider_id' => $hospital->id,
            'doctor_provider_id' => $doctor->id,
            'hospital_department_id' => $department->id,
            'consultation_fee' => 500,
            'is_active' => true,
        ]);
        HospitalDoctor::query()->create([
            'hospital_provider_id' => $hospital->id,
            'doctor_provider_id' => $pendingDoctor->id,
            'hospital_department_id' => $department->id,
            'is_active' => true,
        ]);

        $this->assertSame(1, HospitalDepartment::query()->publiclyVisible()->count());
        $this->assertSame(1, HospitalDoctor::query()->publiclyVisible()->count());

        $this->expectException(ValidationException::class);
        HospitalDoctor::query()->create([
            'hospital_provider_id' => $doctor->id,
            'doctor_provider_id' => $doctor->id,
            'is_active' => true,
        ]);
    }

    public function test_service_catalog_is_internal_safe_and_provider_cannot_set_price_or_other_provider(): void
    {
        $provider = $this->createProvider(ProviderType::Radiology, ProviderStatus::Approved, true);
        $otherProvider = $this->createProvider(ProviderType::Radiology, ProviderStatus::Approved, true);
        $category = ServiceCategory::query()->create([
            'provider_type' => ProviderType::Radiology,
            'code' => 'xray',
            'name_ar' => 'أشعة عادية',
            'name_en' => 'X-Ray',
            'is_active' => true,
        ]);
        $branch = $otherProvider->branches()->create(['name_en' => 'Other Branch']);

        Sanctum::actingAs($provider->owner);
        $this->postJson('/api/v1/provider/services', [
            'provider_id' => $otherProvider->id,
            'service_type' => 'radiology_scan',
            'name_ar' => 'أشعة صدر',
            'base_price' => 1,
        ])->assertUnprocessable();

        $this->postJson('/api/v1/provider/services', [
            'branch_id' => $branch->id,
            'service_category_id' => $category->id,
            'service_type' => 'radiology_scan',
            'name_ar' => 'أشعة صدر',
        ])->assertUnprocessable();

        $this->postJson('/api/v1/provider/services', [
            'service_category_id' => $category->id,
            'service_type' => 'radiology_scan',
            'name_ar' => 'أشعة صدر',
            'duration_minutes' => 15,
        ])->assertCreated();

        $this->assertTrue(ProviderService::query()->where('provider_id', $provider->id)->whereNull('base_price')->exists());

        $patient = User::factory()->create();
        $patient->assignRole(UserRole::Patient->value);
        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/provider/services', [
            'service_type' => 'radiology_scan',
            'name_ar' => 'محاولة مريض',
        ])->assertForbidden();
    }

    public function test_booking_capabilities_and_contract_payment_options_are_safe_public_flags(): void
    {
        $provider = $this->createProvider(ProviderType::Doctor, ProviderStatus::Approved, true);
        ProviderBookingSetting::query()->create([
            'provider_id' => $provider->id,
            'online_video_enabled' => false,
            'pay_at_branch_enabled' => true,
        ]);
        ProviderContract::query()->create([
            'provider_id' => $provider->id,
            'contract_type' => ProviderContractType::CommissionOnly,
            'settlement_cycle' => ProviderSettlementCycle::Monthly,
            'pay_at_branch_allowed' => false,
            'status' => ProviderContractStatus::Active,
        ]);

        $this->getJson('/api/v1/doctors')
            ->assertOk()
            ->assertJsonPath('data.0.booking_capabilities.online_video_enabled', false)
            ->assertJsonPath('data.0.payment_options.pay_at_branch_enabled', false);

        ProviderContract::query()->create([
            'provider_id' => $provider->id,
            'contract_type' => ProviderContractType::Hybrid,
            'settlement_cycle' => ProviderSettlementCycle::Weekly,
            'pay_at_branch_allowed' => true,
            'online_payment_required' => false,
            'status' => ProviderContractStatus::Active,
        ]);
        $provider->bookingSettings()->update(['online_video_enabled' => true]);

        $this->getJson('/api/v1/doctors')
            ->assertOk()
            ->assertJsonPath('data.0.booking_capabilities.online_video_enabled', true)
            ->assertJsonPath('data.0.payment_options.pay_at_branch_enabled', true)
            ->assertJsonMissing(['commission_rate']);

        Sanctum::actingAs($provider->owner);
        $this->putJson('/api/v1/provider/profile', [
            'booking_settings' => ['online_video_enabled' => true],
        ])->assertUnprocessable();
    }

    public function test_admin_can_manage_provider_contract_and_patient_cannot(): void
    {
        $admin = $this->adminUser();
        $patient = User::factory()->create();
        $patient->assignRole(UserRole::Patient->value);
        $provider = $this->createProvider(ProviderType::Gym, ProviderStatus::PendingReview, false);

        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/admin/providers/'.$provider->id.'/contracts', [
            'contract_type' => ProviderContractType::CommissionOnly->value,
            'settlement_cycle' => ProviderSettlementCycle::Weekly->value,
            'status' => ProviderContractStatus::Active->value,
        ])->assertForbidden();

        Sanctum::actingAs($admin);
        $this->postJson('/api/v1/admin/providers/'.$provider->id.'/contracts', [
            'contract_type' => ProviderContractType::Hybrid->value,
            'commission_rate' => 12.5,
            'settlement_cycle' => ProviderSettlementCycle::Weekly->value,
            'pay_at_branch_allowed' => true,
            'online_payment_required' => true,
            'status' => ProviderContractStatus::Active->value,
        ])->assertCreated()
            ->assertJsonPath('data.contract_type', ProviderContractType::Hybrid->value)
            ->assertJsonPath('data.pay_at_branch_allowed', true);

        $this->assertTrue(ProviderContract::query()->where('provider_id', $provider->id)->where('commission_rate', 12.5)->exists());
    }

    private function createProvider(ProviderType $type, ProviderStatus $status, bool $isActive): Provider
    {
        $user = User::factory()->create();
        $user->assignRole(match ($type) {
            ProviderType::Doctor => UserRole::Doctor->value,
            ProviderType::Pharmacy => UserRole::PharmacyAdmin->value,
            ProviderType::Lab => UserRole::LabAdmin->value,
            default => UserRole::ProviderAdmin->value,
        });

        $provider = Provider::query()->create([
            'type' => $type,
            'owner_user_id' => $user->id,
            'name_ar' => 'مزود تجريبي',
            'name_en' => Str::headline($type->value).' Provider '.Str::random(6),
            'status' => $status,
            'is_active' => $isActive,
            'approved_at' => $status === ProviderStatus::Approved ? now() : null,
            'created_by' => $user->id,
        ]);

        $provider->staff()->create([
            'user_id' => $user->id,
            'role' => ProviderStaffRole::Owner,
            'is_owner' => true,
            'status' => 'active',
        ]);

        if ($type === ProviderType::Doctor) {
            DoctorProfile::query()->create([
                'provider_id' => $provider->id,
                'user_id' => $user->id,
                'consultation_fee' => 300,
            ]);
        }

        if ($type === ProviderType::Hospital) {
            HospitalProfile::query()->create([
                'provider_id' => $provider->id,
            ]);
        }

        return $provider->refresh()->load('owner');
    }

    private function createProviderDocument(Provider $provider, ProviderDocumentType $type, ProviderDocumentVisibility $visibility): ProviderDocument
    {
        $file = UploadedFileModel::query()->create([
            'owner_type' => Provider::class,
            'owner_id' => $provider->id,
            'uploaded_by' => $provider->owner_user_id,
            'disk' => 'medical_private',
            'path' => 'private/'.$type->value.'.pdf',
            'original_name' => $type->value.'.pdf',
            'mime_type' => 'application/pdf',
            'size' => 100,
            'file_category' => FileCategory::ProviderDocument,
            'visibility' => FileVisibility::Private,
        ]);

        return ProviderDocument::query()->create([
            'provider_id' => $provider->id,
            'file_id' => $file->id,
            'uploaded_by' => $provider->owner_user_id,
            'document_type' => $type->value,
            'status' => ProviderDocumentStatus::Pending,
            'visibility' => $visibility,
        ]);
    }

    private function adminUser(): User
    {
        $admin = User::factory()->create();
        Role::findOrCreate(UserRole::SuperAdmin->value);
        $admin->assignRole(UserRole::SuperAdmin->value);

        return $admin;
    }
}
