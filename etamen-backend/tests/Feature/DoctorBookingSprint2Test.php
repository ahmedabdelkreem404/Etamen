<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Appointments\Domain\Enums\AppointmentSlotStatus;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Domain\Enums\ConsultationType;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\Appointments\Infrastructure\Models\AppointmentSlot;
use App\Modules\Appointments\Infrastructure\Models\DoctorHoliday;
use App\Modules\Appointments\Infrastructure\Models\DoctorSchedule;
use App\Modules\Appointments\Infrastructure\Models\DoctorScheduleDay;
use App\Modules\AuditLogs\Infrastructure\Models\AuditLog;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\DoctorProfile;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DoctorBookingSprint2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_doctor_owner_can_create_schedule(): void
    {
        ['user' => $doctorUser] = $this->createDoctorProvider();
        Sanctum::actingAs($doctorUser);

        $this->postJson('/api/v1/provider/doctor/schedules', [
            'name' => 'Main clinic schedule',
            'slot_duration_minutes' => 30,
            'buffer_minutes' => 5,
            'max_days_ahead' => 14,
        ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Main clinic schedule');

        $this->assertDatabaseHas('doctor_schedules', [
            'name' => 'Main clinic schedule',
        ]);
    }

    public function test_non_owner_cannot_create_or_update_schedule_for_another_doctor(): void
    {
        ['user' => $firstUser] = $this->createDoctorProvider(email: 'owner-one@example.com');
        ['doctor' => $secondDoctor] = $this->createDoctorProvider(email: 'owner-two@example.com');

        $schedule = DoctorSchedule::query()->create([
            'doctor_profile_id' => $secondDoctor->id,
            'provider_id' => $secondDoctor->provider_id,
            'name' => 'Other schedule',
        ]);

        Sanctum::actingAs($firstUser);

        $this->postJson('/api/v1/provider/doctor/schedules', [
            'doctor_profile_id' => $secondDoctor->id,
            'name' => 'Not mine',
        ])->assertForbidden();

        $this->putJson('/api/v1/provider/doctor/schedules/'.$schedule->id, [
            'name' => 'Hijacked',
        ])->assertForbidden();
    }

    public function test_schedule_day_validates_start_time_before_end_time(): void
    {
        ['user' => $doctorUser, 'doctor' => $doctor] = $this->createDoctorProvider();
        $schedule = DoctorSchedule::query()->create([
            'doctor_profile_id' => $doctor->id,
            'provider_id' => $doctor->provider_id,
        ]);

        Sanctum::actingAs($doctorUser);

        $this->postJson('/api/v1/provider/doctor/schedules/'.$schedule->id.'/days', [
            'day_of_week' => 1,
            'start_time' => '13:00',
            'end_time' => '12:00',
        ])->assertUnprocessable();
    }

    public function test_generate_slots_from_schedule_days_and_does_not_duplicate(): void
    {
        ['user' => $doctorUser, 'doctor' => $doctor] = $this->createDoctorProvider();
        $date = now()->addDays(3)->startOfDay();
        $schedule = $this->createScheduleWithDay($doctor, $date->dayOfWeek);

        Sanctum::actingAs($doctorUser);

        $payload = [
            'start_date' => $date->toDateString(),
            'end_date' => $date->toDateString(),
        ];

        $this->postJson('/api/v1/provider/doctor/slots/generate', $payload)
            ->assertOk()
            ->assertJsonPath('data.created', 2);

        $this->postJson('/api/v1/provider/doctor/slots/generate', $payload)
            ->assertOk()
            ->assertJsonPath('data.created', 0)
            ->assertJsonPath('data.skipped', 2);

        $this->assertSame(2, AppointmentSlot::query()->where('generated_from_schedule_id', $schedule->id)->count());
    }

    public function test_generate_slots_clamps_end_date_to_sixty_days_from_start_date(): void
    {
        ['user' => $doctorUser, 'doctor' => $doctor] = $this->createDoctorProvider();
        $startDate = now()->addDay()->startOfDay();
        $this->createDailySchedule($doctor, maxDaysAhead: 90);

        Sanctum::actingAs($doctorUser);

        $this->postJson('/api/v1/provider/doctor/slots/generate', [
            'start_date' => $startDate->toDateString(),
            'end_date' => $startDate->copy()->addDays(90)->toDateString(),
        ])
            ->assertOk()
            ->assertJsonPath('data.end_date', $startDate->copy()->addDays(60)->toDateString());

        $this->assertFalse(
            AppointmentSlot::query()
                ->where('doctor_profile_id', $doctor->id)
                ->where('starts_at', '>', $startDate->copy()->addDays(60)->endOfDay())
                ->exists(),
        );
    }

    public function test_generate_slots_respects_schedule_max_days_ahead(): void
    {
        ['user' => $doctorUser, 'doctor' => $doctor] = $this->createDoctorProvider();
        $startDate = now()->addDay()->startOfDay();
        $this->createDailySchedule($doctor, maxDaysAhead: 3);

        Sanctum::actingAs($doctorUser);

        $this->postJson('/api/v1/provider/doctor/slots/generate', [
            'start_date' => $startDate->toDateString(),
            'end_date' => $startDate->copy()->addDays(20)->toDateString(),
        ])
            ->assertOk()
            ->assertJsonPath('data.created', 8);

        $this->assertFalse(
            AppointmentSlot::query()
                ->where('doctor_profile_id', $doctor->id)
                ->where('starts_at', '>', $startDate->copy()->addDays(3)->endOfDay())
                ->exists(),
        );
    }

    public function test_slot_generation_counts_preexisting_duplicates_as_skipped_without_crashing(): void
    {
        ['user' => $doctorUser, 'doctor' => $doctor] = $this->createDoctorProvider();
        $date = now()->addDays(3)->startOfDay();
        $this->createScheduleWithDay($doctor, $date->dayOfWeek);

        AppointmentSlot::query()->create([
            'doctor_profile_id' => $doctor->id,
            'provider_id' => $doctor->provider_id,
            'starts_at' => $date->copy()->setTime(9, 0),
            'ends_at' => $date->copy()->setTime(9, 30),
            'status' => AppointmentSlotStatus::Available,
        ]);

        Sanctum::actingAs($doctorUser);

        $this->postJson('/api/v1/provider/doctor/slots/generate', [
            'start_date' => $date->toDateString(),
            'end_date' => $date->toDateString(),
        ])
            ->assertOk()
            ->assertJsonPath('data.created', 1)
            ->assertJsonPath('data.skipped', 1);

        $this->assertSame(2, AppointmentSlot::query()->where('doctor_profile_id', $doctor->id)->count());
    }

    public function test_holiday_blocks_generated_slots(): void
    {
        ['user' => $doctorUser, 'doctor' => $doctor] = $this->createDoctorProvider();
        $date = now()->addDays(4)->startOfDay();
        $this->createScheduleWithDay($doctor, $date->dayOfWeek);

        DoctorHoliday::query()->create([
            'doctor_profile_id' => $doctor->id,
            'provider_id' => $doctor->provider_id,
            'starts_at' => $date->copy()->setTime(8, 30),
            'ends_at' => $date->copy()->setTime(11, 0),
            'reason' => 'Conference',
        ]);

        Sanctum::actingAs($doctorUser);

        $this->postJson('/api/v1/provider/doctor/slots/generate', [
            'start_date' => $date->toDateString(),
            'end_date' => $date->toDateString(),
        ])
            ->assertOk()
            ->assertJsonPath('data.created', 0);
    }

    public function test_public_slots_endpoint_returns_available_slots_for_approved_doctor_only(): void
    {
        ['provider' => $approvedProvider, 'doctor' => $approvedDoctor] = $this->createDoctorProvider();
        ['provider' => $pendingProvider, 'doctor' => $pendingDoctor] = $this->createDoctorProvider(
            status: ProviderStatus::PendingReview,
            isActive: false,
            email: 'pending-doctor@example.com',
        );

        $this->createSlot($approvedDoctor);
        $this->createSlot($pendingDoctor);

        $this->getJson('/api/v1/doctors/'.$approvedProvider->id.'/slots')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson('/api/v1/doctors/'.$pendingProvider->id.'/slots')
            ->assertNotFound();
    }

    public function test_public_slots_endpoint_defaults_to_fourteen_days_and_clamps_query_to_sixty_days(): void
    {
        ['provider' => $provider, 'doctor' => $doctor] = $this->createDoctorProvider();
        $day20Slot = $this->createSlot($doctor, days: 20);
        $day70Slot = $this->createSlot($doctor, days: 70);

        $this->getJson('/api/v1/doctors/'.$provider->id.'/slots')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $response = $this->getJson('/api/v1/doctors/'.$provider->id.'/slots?start_date='.now()->toDateString().'&end_date='.now()->addDays(90)->toDateString())
            ->assertOk();

        $slotIds = collect($response->json('data'))->pluck('id');

        $this->assertTrue($slotIds->contains($day20Slot->id));
        $this->assertFalse($slotIds->contains($day70Slot->id));
    }

    public function test_public_slots_endpoint_does_not_return_unbounded_data(): void
    {
        ['provider' => $provider, 'doctor' => $doctor] = $this->createDoctorProvider();
        $startsAt = now()->addDay()->setTime(9, 0);

        for ($index = 0; $index < 120; $index++) {
            AppointmentSlot::query()->create([
                'doctor_profile_id' => $doctor->id,
                'provider_id' => $doctor->provider_id,
                'starts_at' => $startsAt->copy()->addMinutes($index * 30),
                'ends_at' => $startsAt->copy()->addMinutes(($index + 1) * 30),
                'status' => AppointmentSlotStatus::Available,
            ]);
        }

        $this->getJson('/api/v1/doctors/'.$provider->id.'/slots')
            ->assertOk()
            ->assertJsonCount(100, 'data');
    }

    public function test_suspended_doctor_slots_are_hidden(): void
    {
        ['provider' => $provider, 'doctor' => $doctor] = $this->createDoctorProvider(
            status: ProviderStatus::Suspended,
            isActive: false,
        );
        $this->createSlot($doctor);

        $this->getJson('/api/v1/doctors/'.$provider->id.'/slots')
            ->assertNotFound();
    }

    public function test_patient_can_book_available_slot_and_paid_appointment_becomes_pending_payment(): void
    {
        $patient = $this->patientUser();
        ['doctor' => $doctor] = $this->createDoctorProvider(fee: 300);
        $slot = $this->createSlot($doctor);
        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/appointments', [
            'doctor_profile_id' => $doctor->id,
            'appointment_slot_id' => $slot->id,
            'consultation_type' => ConsultationType::Clinic->value,
            'problem_description' => 'Headache for two days.',
        ])
            ->assertCreated()
            ->assertJsonPath('data.patient_user_id', $patient->id)
            ->assertJsonPath('data.price', '300.00')
            ->assertJsonPath('data.status', AppointmentStatus::PendingPayment->value);

        $this->assertSame(AppointmentSlotStatus::Booked, $slot->refresh()->status);
    }

    public function test_free_appointment_becomes_confirmed(): void
    {
        $patient = $this->patientUser();
        ['doctor' => $doctor] = $this->createDoctorProvider(fee: 0);
        $slot = $this->createSlot($doctor);
        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/appointments', [
            'doctor_profile_id' => $doctor->id,
            'appointment_slot_id' => $slot->id,
            'consultation_type' => ConsultationType::Online->value,
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', AppointmentStatus::Confirmed->value);
    }

    public function test_patient_cannot_book_booked_slot_or_create_duplicate_appointments(): void
    {
        $patient = $this->patientUser();
        ['doctor' => $doctor] = $this->createDoctorProvider(fee: 0);
        $slot = $this->createSlot($doctor);
        Sanctum::actingAs($patient);

        $payload = [
            'doctor_profile_id' => $doctor->id,
            'appointment_slot_id' => $slot->id,
            'consultation_type' => ConsultationType::Clinic->value,
        ];

        $this->postJson('/api/v1/appointments', $payload)->assertCreated();
        $this->postJson('/api/v1/appointments', $payload)->assertUnprocessable();

        $this->assertSame(1, Appointment::query()->where('appointment_slot_id', $slot->id)->count());
    }

    public function test_slot_and_appointment_unique_constraints_exist(): void
    {
        ['doctor' => $doctor] = $this->createDoctorProvider();
        $slot = $this->createSlot($doctor);

        $this->expectException(QueryException::class);

        AppointmentSlot::query()->create([
            'doctor_profile_id' => $doctor->id,
            'provider_id' => $doctor->provider_id,
            'starts_at' => $slot->starts_at,
            'ends_at' => $slot->ends_at,
            'status' => AppointmentSlotStatus::Available,
        ]);
    }

    public function test_appointment_slot_id_unique_constraint_exists(): void
    {
        $patient = $this->patientUser('patient-one@example.com');
        $otherPatient = $this->patientUser('patient-two@example.com');
        ['doctor' => $doctor] = $this->createDoctorProvider();
        $slot = $this->createSlot($doctor);

        $this->createAppointment($patient, $doctor, AppointmentStatus::Confirmed, 0, $slot);

        $this->expectException(QueryException::class);

        $this->createAppointment($otherPatient, $doctor, AppointmentStatus::Confirmed, 0, $slot);
    }

    public function test_patient_can_view_and_cancel_own_appointment_only(): void
    {
        $patient = $this->patientUser('patient-one@example.com');
        $otherPatient = $this->patientUser('patient-two@example.com');
        ['doctor' => $doctor] = $this->createDoctorProvider(fee: 0);
        $appointment = $this->createAppointment($patient, $doctor, AppointmentStatus::Confirmed);

        Sanctum::actingAs($patient);
        $this->getJson('/api/v1/appointments/'.$appointment->id)->assertOk();

        Sanctum::actingAs($otherPatient);
        $this->getJson('/api/v1/appointments/'.$appointment->id)->assertForbidden();

        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/appointments/'.$appointment->id.'/cancel', ['reason' => 'Cannot attend'])
            ->assertOk()
            ->assertJsonPath('data.status', AppointmentStatus::CancelledByPatient->value);

        $this->assertSame(AppointmentSlotStatus::Available, $appointment->slot->refresh()->status);
    }

    public function test_patient_cannot_cancel_completed_appointment_or_manually_set_status(): void
    {
        $patient = $this->patientUser();
        ['doctor' => $doctor] = $this->createDoctorProvider(fee: 0);
        $appointment = $this->createAppointment($patient, $doctor, AppointmentStatus::Completed);
        $slot = $this->createSlot($doctor, days: 5);

        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/appointments/'.$appointment->id.'/cancel')
            ->assertUnprocessable();

        $this->postJson('/api/v1/appointments', [
            'doctor_profile_id' => $doctor->id,
            'appointment_slot_id' => $slot->id,
            'consultation_type' => ConsultationType::Clinic->value,
            'status' => AppointmentStatus::Confirmed->value,
        ])->assertUnprocessable();
    }

    public function test_doctor_can_view_own_appointments_but_not_another_doctor_appointment(): void
    {
        ['user' => $firstDoctorUser, 'doctor' => $firstDoctor] = $this->createDoctorProvider(email: 'first-doctor@example.com');
        ['doctor' => $secondDoctor] = $this->createDoctorProvider(email: 'second-doctor@example.com');
        $patient = $this->patientUser();
        $ownAppointment = $this->createAppointment($patient, $firstDoctor, AppointmentStatus::Confirmed);
        $otherAppointment = $this->createAppointment($patient, $secondDoctor, AppointmentStatus::Confirmed);

        Sanctum::actingAs($firstDoctorUser);

        $this->getJson('/api/v1/provider/appointments')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownAppointment->id);

        $this->getJson('/api/v1/provider/appointments/'.$otherAppointment->id)
            ->assertForbidden();
    }

    public function test_doctor_lifecycle_rules_are_enforced(): void
    {
        ['user' => $doctorUser, 'doctor' => $doctor] = $this->createDoctorProvider(fee: 0);
        $patient = $this->patientUser();
        $confirmed = $this->createAppointment($patient, $doctor, AppointmentStatus::Confirmed);
        $pendingPayment = $this->createAppointment($patient, $doctor, AppointmentStatus::PendingPayment, 300, null, 5);

        Sanctum::actingAs($doctorUser);

        $this->postJson('/api/v1/provider/appointments/'.$pendingPayment->id.'/accept')
            ->assertUnprocessable();

        $this->postJson('/api/v1/provider/appointments/'.$confirmed->id.'/accept')
            ->assertOk()
            ->assertJsonPath('data.status', AppointmentStatus::Accepted->value);

        $this->postJson('/api/v1/provider/appointments/'.$pendingPayment->id.'/complete')
            ->assertUnprocessable();

        $this->postJson('/api/v1/provider/appointments/'.$confirmed->id.'/complete')
            ->assertOk()
            ->assertJsonPath('data.status', AppointmentStatus::Completed->value);
    }

    public function test_doctor_rejecting_appointment_releases_slot(): void
    {
        ['user' => $doctorUser, 'doctor' => $doctor] = $this->createDoctorProvider(fee: 0);
        $patient = $this->patientUser();
        $appointment = $this->createAppointment($patient, $doctor, AppointmentStatus::Confirmed);

        Sanctum::actingAs($doctorUser);

        $this->postJson('/api/v1/provider/appointments/'.$appointment->id.'/reject', [
            'reason' => 'Doctor unavailable.',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', AppointmentStatus::Rejected->value);

        $this->assertSame(AppointmentSlotStatus::Available, $appointment->slot->refresh()->status);
    }

    public function test_admin_can_view_all_and_force_cancel_with_reason(): void
    {
        $admin = $this->adminUser();
        $patient = $this->patientUser();
        ['doctor' => $doctor] = $this->createDoctorProvider();
        $appointment = $this->createAppointment($patient, $doctor, AppointmentStatus::Confirmed);

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin/appointments')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->postJson('/api/v1/admin/appointments/'.$appointment->id.'/force-cancel', [
            'reason' => 'Admin verified clinic closure.',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', AppointmentStatus::CancelledByDoctor->value);
    }

    public function test_non_admin_cannot_access_admin_appointment_endpoints(): void
    {
        Sanctum::actingAs($this->patientUser());

        $this->getJson('/api/v1/admin/appointments')->assertForbidden();
    }

    public function test_reviews_allowed_only_after_completed_appointment_once_with_valid_rating(): void
    {
        $patient = $this->patientUser();
        ['doctor' => $doctor] = $this->createDoctorProvider();
        $completed = $this->createAppointment($patient, $doctor, AppointmentStatus::Completed);
        $confirmed = $this->createAppointment($patient, $doctor, AppointmentStatus::Confirmed, 0, null, 5);

        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/appointments/'.$confirmed->id.'/review', [
            'rating' => 5,
        ])->assertUnprocessable();

        $this->postJson('/api/v1/appointments/'.$completed->id.'/review', [
            'rating' => 6,
        ])->assertUnprocessable();

        $this->postJson('/api/v1/appointments/'.$completed->id.'/review', [
            'rating' => 5,
            'comment' => 'Helpful visit.',
        ])->assertCreated();

        $this->postJson('/api/v1/appointments/'.$completed->id.'/review', [
            'rating' => 4,
        ])->assertUnprocessable();
    }

    public function test_booking_does_not_trust_patient_user_id_or_price_from_request(): void
    {
        $patient = $this->patientUser('real-patient@example.com');
        $otherPatient = $this->patientUser('fake-patient@example.com');
        ['doctor' => $doctor] = $this->createDoctorProvider(fee: 300);
        $slot = $this->createSlot($doctor);

        Sanctum::actingAs($patient);

        $this->postJson('/api/v1/appointments', [
            'patient_user_id' => $otherPatient->id,
            'doctor_profile_id' => $doctor->id,
            'appointment_slot_id' => $slot->id,
            'consultation_type' => ConsultationType::Clinic->value,
            'price' => 0,
        ])->assertUnprocessable();

        $this->assertSame(0, Appointment::query()->count());
    }

    public function test_status_histories_and_audit_logs_are_created_for_sensitive_actions(): void
    {
        ['user' => $doctorUser, 'doctor' => $doctor] = $this->createDoctorProvider(fee: 0);
        $patient = $this->patientUser();
        $slot = $this->createSlot($doctor);

        Sanctum::actingAs($patient);
        $appointmentId = $this->postJson('/api/v1/appointments', [
            'doctor_profile_id' => $doctor->id,
            'appointment_slot_id' => $slot->id,
            'consultation_type' => ConsultationType::Clinic->value,
        ])->assertCreated()->json('data.id');

        Sanctum::actingAs($doctorUser);
        $this->postJson('/api/v1/provider/appointments/'.$appointmentId.'/accept')->assertOk();

        $appointment = Appointment::query()->findOrFail($appointmentId);

        $this->assertSame(2, $appointment->statusHistories()->count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'appointment.booked']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'doctor_appointment.accepted']);
        $this->assertGreaterThanOrEqual(2, AuditLog::query()->count());
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

    private function createDoctorProvider(
        ProviderStatus $status = ProviderStatus::Approved,
        bool $isActive = true,
        int|float $fee = 300,
        ?string $email = null,
    ): array {
        $user = User::factory()->create(['email' => $email ?? 'doctor-'.Str::random(8).'@example.com']);
        $user->assignRole(UserRole::Doctor->value);

        $provider = Provider::query()->create([
            'type' => ProviderType::Doctor,
            'owner_user_id' => $user->id,
            'name_en' => 'Doctor Provider '.Str::random(6),
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

        $doctor = DoctorProfile::query()->create([
            'provider_id' => $provider->id,
            'user_id' => $user->id,
            'consultation_fee' => $fee,
        ]);

        return [
            'user' => $user,
            'provider' => $provider->refresh(),
            'doctor' => $doctor->refresh(),
        ];
    }

    private function createScheduleWithDay(DoctorProfile $doctor, int $dayOfWeek): DoctorSchedule
    {
        $schedule = DoctorSchedule::query()->create([
            'doctor_profile_id' => $doctor->id,
            'provider_id' => $doctor->provider_id,
            'slot_duration_minutes' => 30,
            'buffer_minutes' => 0,
            'max_days_ahead' => 14,
        ]);

        DoctorScheduleDay::query()->create([
            'doctor_schedule_id' => $schedule->id,
            'day_of_week' => $dayOfWeek,
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        return $schedule;
    }

    private function createDailySchedule(DoctorProfile $doctor, int $maxDaysAhead): DoctorSchedule
    {
        $schedule = DoctorSchedule::query()->create([
            'doctor_profile_id' => $doctor->id,
            'provider_id' => $doctor->provider_id,
            'slot_duration_minutes' => 30,
            'buffer_minutes' => 0,
            'max_days_ahead' => $maxDaysAhead,
        ]);

        for ($day = 0; $day <= 6; $day++) {
            DoctorScheduleDay::query()->create([
                'doctor_schedule_id' => $schedule->id,
                'day_of_week' => $day,
                'start_time' => '09:00',
                'end_time' => '10:00',
            ]);
        }

        return $schedule;
    }

    private function createSlot(DoctorProfile $doctor, int $days = 3): AppointmentSlot
    {
        $startsAt = now()->addDays($days)->startOfDay()->setTime(9, 0);

        while (AppointmentSlot::query()->where('doctor_profile_id', $doctor->id)->where('starts_at', $startsAt)->exists()) {
            $startsAt = $startsAt->addMinutes(30);
        }

        return AppointmentSlot::query()->create([
            'doctor_profile_id' => $doctor->id,
            'provider_id' => $doctor->provider_id,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addMinutes(30),
            'status' => AppointmentSlotStatus::Available,
        ]);
    }

    private function createAppointment(
        User $patient,
        DoctorProfile $doctor,
        AppointmentStatus $status,
        int|float $price = 0,
        ?AppointmentSlot $slot = null,
        int $days = 3,
    ): Appointment {
        $slot ??= $this->createSlot($doctor, $days);
        $slot->update(['status' => AppointmentSlotStatus::Booked]);

        $appointment = Appointment::query()->create([
            'appointment_number' => 'APT-TEST-'.Str::upper(Str::random(8)),
            'patient_user_id' => $patient->id,
            'doctor_profile_id' => $doctor->id,
            'provider_id' => $doctor->provider_id,
            'appointment_slot_id' => $slot->id,
            'consultation_type' => ConsultationType::Clinic,
            'price' => $price,
            'currency' => 'EGP',
            'status' => $status,
            'booked_at' => now(),
            'confirmed_at' => in_array($status, [AppointmentStatus::Confirmed, AppointmentStatus::Accepted, AppointmentStatus::Completed, AppointmentStatus::NoShow], true) ? now() : null,
            'accepted_at' => in_array($status, [AppointmentStatus::Accepted, AppointmentStatus::Completed, AppointmentStatus::NoShow], true) ? now() : null,
            'completed_at' => $status === AppointmentStatus::Completed ? now() : null,
            'no_show_at' => $status === AppointmentStatus::NoShow ? now() : null,
        ]);

        $appointment->statusHistories()->create([
            'from_status' => null,
            'to_status' => $status->value,
            'actor_id' => $patient->id,
        ]);

        return $appointment->refresh();
    }
}
