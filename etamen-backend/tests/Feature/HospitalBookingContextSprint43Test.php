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
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\DoctorProfile;
use App\Modules\Providers\Infrastructure\Models\HospitalDepartment;
use App\Modules\Providers\Infrastructure\Models\HospitalDoctor;
use App\Modules\Providers\Infrastructure\Models\HospitalProfile;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HospitalBookingContextSprint43Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_direct_doctor_booking_still_works_without_hospital_context(): void
    {
        $patient = $this->patientUser();
        ['doctor' => $doctor] = $this->doctorProvider(fee: 300);
        $slot = $this->availableSlot($doctor);

        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/appointments', $this->bookingPayload($doctor, $slot))
            ->assertCreated()
            ->assertJsonPath('data.booked_through_hospital', false)
            ->assertJsonPath('data.hospital_provider_id', null)
            ->assertJsonPath('data.price', '300.00');

        $appointment = Appointment::query()->firstOrFail();
        $this->assertNull($appointment->hospital_provider_id);
        $this->assertNull($appointment->hospital_department_id);
        $this->assertNull($appointment->hospital_doctor_id);
    }

    public function test_hospital_booking_stores_validated_context_and_uses_hospital_fee(): void
    {
        $patient = $this->patientUser();
        $context = $this->hospitalContext(doctorFee: 300, hospitalFee: 450);
        $slot = $this->availableSlot($context['doctor']);

        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/appointments', [
            ...$this->bookingPayload($context['doctor'], $slot),
            'hospital_provider_id' => $context['hospital']->id,
            'hospital_department_id' => $context['department']->id,
        ])
            ->assertCreated()
            ->assertJsonPath('data.booked_through_hospital', true)
            ->assertJsonPath('data.hospital_provider_id', $context['hospital']->id)
            ->assertJsonPath('data.hospital_department_id', $context['department']->id)
            ->assertJsonPath('data.hospital_doctor_id', $context['hospitalDoctor']->id)
            ->assertJsonPath('data.hospital.name_en', 'Etamen Specialty Hospital')
            ->assertJsonPath('data.department.name_en', 'Cardiology')
            ->assertJsonPath('data.price', '450.00');

        $appointment = Appointment::query()->with('payment')->firstOrFail();
        $this->assertSame($context['hospital']->id, $appointment->hospital_provider_id);
        $this->assertSame($context['department']->id, $appointment->hospital_department_id);
        $this->assertSame($context['hospitalDoctor']->id, $appointment->hospital_doctor_id);
        $this->assertSame('450.00', $appointment->price);
        $this->assertSame('450.00', $appointment->payment?->amount);
    }

    public function test_hospital_booking_falls_back_to_doctor_fee_when_link_fee_is_null(): void
    {
        $patient = $this->patientUser();
        $context = $this->hospitalContext(doctorFee: 320, hospitalFee: null);
        $slot = $this->availableSlot($context['doctor']);

        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/appointments', [
            ...$this->bookingPayload($context['doctor'], $slot),
            'hospital_doctor_id' => $context['hospitalDoctor']->id,
        ])
            ->assertCreated()
            ->assertJsonPath('data.price', '320.00');

        $this->assertSame('320.00', Appointment::query()->firstOrFail()->price);
    }

    public function test_invalid_hospital_context_is_rejected_before_booking_or_payment_creation(): void
    {
        $patient = $this->patientUser();
        $context = $this->hospitalContext();
        ['doctor' => $unlinkedDoctor] = $this->doctorProvider(fee: 260);
        $slot = $this->availableSlot($unlinkedDoctor);

        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/appointments', [
            ...$this->bookingPayload($unlinkedDoctor, $slot),
            'hospital_provider_id' => $context['hospital']->id,
            'hospital_department_id' => $context['department']->id,
        ])->assertUnprocessable();

        $this->assertSame(0, Appointment::query()->count());
        $this->assertSame(0, Payment::query()->count());
        $this->assertSame(AppointmentSlotStatus::Available, $slot->refresh()->status);
    }

    public function test_pending_hospital_or_inactive_hospital_doctor_link_is_rejected(): void
    {
        $patient = $this->patientUser();
        $pendingContext = $this->hospitalContext(hospitalStatus: ProviderStatus::PendingReview, hospitalActive: false);
        $pendingSlot = $this->availableSlot($pendingContext['doctor']);

        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/appointments', [
            ...$this->bookingPayload($pendingContext['doctor'], $pendingSlot),
            'hospital_doctor_id' => $pendingContext['hospitalDoctor']->id,
        ])->assertUnprocessable();

        $activeContext = $this->hospitalContext();
        $activeContext['hospitalDoctor']->update(['is_active' => false]);
        $slot = $this->availableSlot($activeContext['doctor']);

        $this->postJson('/api/v1/appointments', [
            ...$this->bookingPayload($activeContext['doctor'], $slot),
            'hospital_doctor_id' => $activeContext['hospitalDoctor']->id,
        ])->assertUnprocessable();
    }

    public function test_slot_must_belong_to_selected_doctor_even_with_valid_hospital_context(): void
    {
        $patient = $this->patientUser();
        $context = $this->hospitalContext();
        ['doctor' => $otherDoctor] = $this->doctorProvider(fee: 220);
        $otherSlot = $this->availableSlot($otherDoctor);

        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/appointments', [
            ...$this->bookingPayload($context['doctor'], $otherSlot),
            'hospital_doctor_id' => $context['hospitalDoctor']->id,
        ])->assertUnprocessable();

        $this->assertSame(AppointmentSlotStatus::Available, $otherSlot->refresh()->status);
    }

    public function test_frontend_price_cannot_be_forced_for_hospital_booking(): void
    {
        $patient = $this->patientUser();
        $context = $this->hospitalContext(doctorFee: 300, hospitalFee: 450);
        $slot = $this->availableSlot($context['doctor']);

        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/appointments', [
            ...$this->bookingPayload($context['doctor'], $slot),
            'hospital_doctor_id' => $context['hospitalDoctor']->id,
            'price' => 1,
        ])->assertUnprocessable();

        $this->assertSame(0, Appointment::query()->count());
    }

    public function test_patient_response_is_safe_and_admin_hospital_summary_counts_context_appointments(): void
    {
        $patient = $this->patientUser();
        $admin = $this->adminUser();
        $context = $this->hospitalContext(doctorFee: 300, hospitalFee: 450);
        $slot = $this->availableSlot($context['doctor']);

        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/appointments', [
            ...$this->bookingPayload($context['doctor'], $slot),
            'hospital_provider_id' => $context['hospital']->id,
            'hospital_department_id' => $context['department']->id,
        ])->assertCreated();

        $appointmentId = Appointment::query()->value('id');

        $this->getJson('/api/v1/appointments/'.$appointmentId)
            ->assertOk()
            ->assertJsonPath('data.booked_through_hospital', true)
            ->assertJsonMissing(['medical_private'])
            ->assertJsonMissing(['private'])
            ->assertJsonMissing(['contract_type']);

        Sanctum::actingAs($admin);
        $this->getJson('/api/v1/admin/hospitals/'.$context['hospital']->id.'/summary')
            ->assertOk()
            ->assertJsonPath('data.total_appointments', 1)
            ->assertJsonPath('data.pending_payment', 1)
            ->assertJsonPath('data.gross_amount', '450.00');

        $this->getJson('/api/v1/admin/hospitals/'.$context['hospital']->id.'/appointments')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.hospital_provider_id', $context['hospital']->id);
    }

    private function bookingPayload(DoctorProfile $doctor, AppointmentSlot $slot): array
    {
        return [
            'doctor_profile_id' => $doctor->id,
            'appointment_slot_id' => $slot->id,
            'consultation_type' => ConsultationType::Clinic->value,
        ];
    }

    private function hospitalContext(
        int|float $doctorFee = 300,
        int|float|null $hospitalFee = 450,
        ProviderStatus $hospitalStatus = ProviderStatus::Approved,
        bool $hospitalActive = true,
    ): array {
        $hospital = $this->hospitalProvider(status: $hospitalStatus, isActive: $hospitalActive);
        ['provider' => $doctorProvider, 'doctor' => $doctor] = $this->doctorProvider(fee: $doctorFee);
        $department = HospitalDepartment::query()->create([
            'hospital_provider_id' => $hospital->id,
            'name_ar' => 'Cardiology AR',
            'name_en' => 'Cardiology',
            'is_active' => true,
        ]);
        $hospitalDoctor = HospitalDoctor::query()->create([
            'hospital_provider_id' => $hospital->id,
            'doctor_provider_id' => $doctorProvider->id,
            'hospital_department_id' => $department->id,
            'consultation_fee' => $hospitalFee,
            'online_consultation_enabled' => false,
            'clinic_consultation_enabled' => true,
            'is_active' => true,
        ]);

        return compact('hospital', 'doctorProvider', 'doctor', 'department', 'hospitalDoctor');
    }

    private function hospitalProvider(
        ProviderStatus $status = ProviderStatus::Approved,
        bool $isActive = true,
    ): Provider {
        $hospital = $this->provider(ProviderType::Hospital, $status, $isActive, 'hospital-'.Str::random(8));
        HospitalProfile::query()->create([
            'provider_id' => $hospital->id,
            'description_ar' => 'Demo hospital.',
            'description_en' => 'Demo hospital.',
            'emergency_available' => true,
            'has_outpatient' => true,
            'has_inpatient' => true,
            'has_icu' => true,
            'has_ambulance' => true,
            'is_active' => true,
        ]);

        return $hospital->refresh();
    }

    private function doctorProvider(
        ProviderStatus $status = ProviderStatus::Approved,
        bool $isActive = true,
        int|float $fee = 300,
    ): array {
        $provider = $this->provider(ProviderType::Doctor, $status, $isActive, 'doctor-'.Str::random(8));
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
        string $slug,
    ): Provider {
        $user = User::factory()->create();
        $user->assignRole(match ($type) {
            ProviderType::Doctor => UserRole::Doctor->value,
            default => UserRole::ProviderAdmin->value,
        });

        $provider = Provider::query()->create([
            'type' => $type,
            'owner_user_id' => $user->id,
            'name_ar' => $type === ProviderType::Hospital ? 'Hospital AR' : 'Doctor AR',
            'name_en' => $type === ProviderType::Hospital ? 'Etamen Specialty Hospital' : 'Dr Demo',
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
        $user = User::factory()->create(['email' => 'hospital-context-patient-'.Str::random(6).'@example.test']);
        $user->assignRole(UserRole::Patient->value);

        return $user;
    }

    private function adminUser(): User
    {
        $user = User::factory()->create(['email' => 'hospital-context-admin-'.Str::random(6).'@example.test']);
        $user->assignRole(UserRole::SuperAdmin->value);

        return $user;
    }
}
