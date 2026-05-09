<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Appointments\Domain\Enums\AppointmentSlotStatus;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Domain\Enums\ConsultationType;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\Appointments\Infrastructure\Models\AppointmentSlot;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\MedicalFiles\Domain\Enums\FileCategory;
use App\Modules\MedicalFiles\Domain\Enums\FileVisibility;
use App\Modules\MedicalFiles\Infrastructure\Models\UploadedFile as UploadedFileModel;
use App\Modules\Providers\Domain\Enums\ProviderDocumentStatus;
use App\Modules\Providers\Domain\Enums\ProviderDocumentType;
use App\Modules\Providers\Domain\Enums\ProviderDocumentVisibility;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\DoctorProfile;
use App\Modules\Providers\Infrastructure\Models\HospitalDepartment;
use App\Modules\Providers\Infrastructure\Models\HospitalDoctor;
use App\Modules\Providers\Infrastructure\Models\HospitalProfile;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HospitalPublicSectionSprint42Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_approved_active_hospital_appears_publicly_with_safe_summary(): void
    {
        ['hospital' => $hospital, 'department' => $department, 'doctorProvider' => $doctorProvider] = $this->hospitalWithDepartmentDoctor();

        $this->getJson('/api/v1/hospitals')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $hospital->id)
            ->assertJsonPath('data.0.name_ar', 'مستشفى اطمن التخصصي')
            ->assertJsonPath('data.0.departments_count', 1)
            ->assertJsonPath('data.0.doctors_count', 1)
            ->assertJsonPath('data.0.emergency_available', true)
            ->assertJsonMissing(['license_number'])
            ->assertJsonMissing(['reviewed_by'])
            ->assertJsonMissing(['private/national-id.pdf']);

        $this->getJson('/api/v1/hospitals/'.$hospital->id)
            ->assertOk()
            ->assertJsonPath('data.id', $hospital->id)
            ->assertJsonPath('data.branches.0.address_ar', 'شارع تجريبي، مدينة نصر، القاهرة')
            ->assertJsonMissing(['tax_card'])
            ->assertJsonMissing(['commercial_register']);

        $this->getJson('/api/v1/hospitals/'.$hospital->id.'/departments')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $department->id)
            ->assertJsonPath('data.0.doctors_count', 1);

        $this->getJson('/api/v1/hospitals/'.$hospital->id.'/departments/'.$department->id.'/doctors')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $doctorProvider->id)
            ->assertJsonPath('data.0.doctor_profile.consultation_fee', '300.00');
    }

    public function test_pending_rejected_suspended_and_wrong_provider_types_are_hidden(): void
    {
        $approvedHospital = $this->hospitalWithDepartmentDoctor()['hospital'];
        $pendingHospital = $this->hospitalProvider(status: ProviderStatus::PendingReview, isActive: false, slug: 'pending-hospital');
        $rejectedHospital = $this->hospitalProvider(status: ProviderStatus::Rejected, isActive: false, slug: 'rejected-hospital');
        $suspendedHospital = $this->hospitalProvider(status: ProviderStatus::Suspended, isActive: false, slug: 'suspended-hospital');
        $clinic = $this->provider(ProviderType::Clinic, ProviderStatus::Approved, true);

        $response = $this->getJson('/api/v1/hospitals')->assertOk();
        $ids = collect($response->json('data'))->pluck('id');

        $this->assertTrue($ids->contains($approvedHospital->id));
        $this->assertFalse($ids->contains($pendingHospital->id));
        $this->assertFalse($ids->contains($rejectedHospital->id));
        $this->assertFalse($ids->contains($suspendedHospital->id));

        $this->getJson('/api/v1/hospitals/'.$clinic->id)->assertNotFound();
    }

    public function test_inactive_departments_and_pending_doctor_links_are_hidden(): void
    {
        ['hospital' => $hospital, 'department' => $department] = $this->hospitalWithDepartmentDoctor();
        $inactiveDepartment = HospitalDepartment::query()->create([
            'hospital_provider_id' => $hospital->id,
            'name_ar' => 'قسم غير نشط',
            'name_en' => 'Inactive',
            'is_active' => false,
        ]);
        $pendingDoctor = $this->doctorProvider(status: ProviderStatus::PendingReview, isActive: false, fee: 240);
        HospitalDoctor::query()->create([
            'hospital_provider_id' => $hospital->id,
            'doctor_provider_id' => $pendingDoctor['provider']->id,
            'hospital_department_id' => $department->id,
            'is_active' => true,
        ]);

        $this->getJson('/api/v1/hospitals/'.$hospital->id.'/departments')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonMissing(['Inactive'])
            ->assertJsonMissing(['قسم غير نشط']);

        $this->getJson('/api/v1/hospitals/'.$hospital->id.'/departments/'.$inactiveDepartment->id.'/doctors')
            ->assertNotFound();

        $doctorResponse = $this->getJson('/api/v1/hospitals/'.$hospital->id.'/departments/'.$department->id.'/doctors')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $doctorIds = collect($doctorResponse->json('data'))->pluck('id');
        $this->assertFalse($doctorIds->contains($pendingDoctor['provider']->id));
    }

    public function test_public_hospital_api_never_exposes_private_document_paths(): void
    {
        $hospital = $this->hospitalWithDepartmentDoctor()['hospital'];

        $file = UploadedFileModel::query()->create([
            'owner_type' => Provider::class,
            'owner_id' => $hospital->id,
            'uploaded_by' => $hospital->owner_user_id,
            'disk' => 'medical_private',
            'path' => 'private/national-id.pdf',
            'original_name' => 'national-id.pdf',
            'mime_type' => 'application/pdf',
            'size' => 200,
            'file_category' => FileCategory::ProviderDocument,
            'visibility' => FileVisibility::Private,
        ]);
        ProviderDocument::query()->create([
            'provider_id' => $hospital->id,
            'file_id' => $file->id,
            'uploaded_by' => $hospital->owner_user_id,
            'document_type' => ProviderDocumentType::NationalId,
            'status' => ProviderDocumentStatus::Approved,
            'visibility' => ProviderDocumentVisibility::AdminOnly,
        ]);

        $this->getJson('/api/v1/hospitals/'.$hospital->id)
            ->assertOk()
            ->assertJsonMissing(['private/national-id.pdf'])
            ->assertJsonMissing(['medical_private'])
            ->assertJsonMissing(['national_id']);
    }

    public function test_booking_hospital_discovered_doctor_still_uses_backend_doctor_price(): void
    {
        $patient = $this->patientUser();
        ['doctor' => $doctor] = $this->hospitalWithDepartmentDoctor(hospitalConsultationFee: 50);
        $slot = $this->availableSlot($doctor);

        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/appointments', [
            'doctor_profile_id' => $doctor->id,
            'appointment_slot_id' => $slot->id,
            'consultation_type' => ConsultationType::Clinic->value,
        ])
            ->assertCreated()
            ->assertJsonPath('data.price', '300.00')
            ->assertJsonPath('data.status', AppointmentStatus::PendingPayment->value);

        $this->assertSame(1, Appointment::query()->count());
        $this->assertSame(AppointmentSlotStatus::Booked, $slot->refresh()->status);
    }

    private function hospitalWithDepartmentDoctor(int|float $hospitalConsultationFee = 300): array
    {
        $hospital = $this->hospitalProvider();
        ['provider' => $doctorProvider, 'doctor' => $doctor] = $this->doctorProvider();
        $department = HospitalDepartment::query()->create([
            'hospital_provider_id' => $hospital->id,
            'name_ar' => 'قلب وأوعية دموية',
            'name_en' => 'Cardiology',
            'is_active' => true,
        ]);
        HospitalDoctor::query()->create([
            'hospital_provider_id' => $hospital->id,
            'doctor_provider_id' => $doctorProvider->id,
            'hospital_department_id' => $department->id,
            'consultation_fee' => $hospitalConsultationFee,
            'online_consultation_enabled' => false,
            'clinic_consultation_enabled' => true,
            'is_active' => true,
        ]);

        return [
            'hospital' => $hospital,
            'department' => $department,
            'doctorProvider' => $doctorProvider,
            'doctor' => $doctor,
        ];
    }

    private function hospitalProvider(
        ProviderStatus $status = ProviderStatus::Approved,
        bool $isActive = true,
        string $slug = 'etamen-specialty-hospital',
    ): Provider {
        $hospital = $this->provider(ProviderType::Hospital, $status, $isActive, slug: $slug);
        HospitalProfile::query()->create([
            'provider_id' => $hospital->id,
            'description_ar' => 'مستشفى تجريبي آمن.',
            'description_en' => 'Safe demo hospital.',
            'emergency_available' => true,
            'has_outpatient' => true,
            'has_inpatient' => true,
            'has_icu' => true,
            'has_ambulance' => true,
            'is_active' => true,
        ]);
        $hospital->branches()->create([
            'name_ar' => 'مستشفى اطمن التخصصي - مدينة نصر',
            'name_en' => 'Etamen Specialty Hospital - Nasr City',
            'address_ar' => 'شارع تجريبي، مدينة نصر، القاهرة',
            'address_en' => 'Demo Street, Nasr City, Cairo',
            'address_line_1' => 'Demo Street',
            'district' => 'Nasr City',
            'latitude' => 30.0561,
            'longitude' => 31.3300,
            'phone' => '01000006001',
            'is_main' => true,
            'is_active' => true,
        ]);

        return $hospital->refresh();
    }

    private function doctorProvider(
        ProviderStatus $status = ProviderStatus::Approved,
        bool $isActive = true,
        int|float $fee = 300,
    ): array {
        $provider = $this->provider(ProviderType::Doctor, $status, $isActive, slug: 'doctor-'.Str::random(8));
        $doctor = DoctorProfile::query()->create([
            'provider_id' => $provider->id,
            'user_id' => $provider->owner_user_id,
            'consultation_fee' => $fee,
            'years_of_experience' => 10,
        ]);

        return [
            'provider' => $provider->refresh(),
            'doctor' => $doctor->refresh(),
        ];
    }

    private function provider(
        ProviderType $type,
        ProviderStatus $status,
        bool $isActive,
        ?string $slug = null,
    ): Provider {
        $user = User::factory()->create();
        $user->assignRole(match ($type) {
            ProviderType::Doctor => UserRole::Doctor->value,
            default => UserRole::ProviderAdmin->value,
        });

        $provider = Provider::query()->create([
            'type' => $type,
            'owner_user_id' => $user->id,
            'name_ar' => $type === ProviderType::Hospital ? 'مستشفى اطمن التخصصي' : 'د. أحمد التجريبي',
            'name_en' => $type === ProviderType::Hospital ? 'Etamen Specialty Hospital' : 'Dr Ahmed Demo',
            'slug' => $slug,
            'phone' => '01000000000',
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

        return $provider->refresh();
    }

    private function availableSlot(DoctorProfile $doctor): AppointmentSlot
    {
        return AppointmentSlot::query()->create([
            'doctor_profile_id' => $doctor->id,
            'provider_id' => $doctor->provider_id,
            'starts_at' => now()->addDays(2)->setTime(10, 0),
            'ends_at' => now()->addDays(2)->setTime(10, 30),
            'status' => AppointmentSlotStatus::Available,
        ]);
    }

    private function patientUser(): User
    {
        $user = User::factory()->create(['email' => 'hospital-patient@example.test']);
        $user->assignRole(UserRole::Patient->value);

        return $user;
    }
}
