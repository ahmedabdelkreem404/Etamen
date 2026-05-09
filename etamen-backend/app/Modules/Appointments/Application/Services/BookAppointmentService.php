<?php

namespace App\Modules\Appointments\Application\Services;

use App\Models\User;
use App\Modules\Appointments\Domain\Enums\AppointmentSlotStatus;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\Appointments\Infrastructure\Models\AppointmentSlot;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Payments\Application\Services\PaymentCreationService;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\DoctorProfile;
use App\Modules\Providers\Infrastructure\Models\HospitalDoctor;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookAppointmentService
{
    public function __construct(
        private readonly AppointmentNumberGenerator $numberGenerator,
        private readonly AuditLogService $auditLogService,
        private readonly PaymentCreationService $paymentCreationService,
    ) {}

    public function book(User $patient, array $data): Appointment
    {
        if (! $patient->hasRole(UserRole::Patient->value)) {
            throw new AuthorizationException('Only patients can book appointments.');
        }

        return DB::transaction(function () use ($patient, $data): Appointment {
            $doctor = DoctorProfile::query()
                ->with('provider')
                ->findOrFail($data['doctor_profile_id']);

            $this->ensureDoctorIsBookable($doctor);

            $slot = AppointmentSlot::query()
                ->whereKey($data['appointment_slot_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $slot->doctor_profile_id !== (int) $doctor->id) {
                throw ValidationException::withMessages([
                    'appointment_slot_id' => ['The selected slot does not belong to this doctor.'],
                ]);
            }

            if ($slot->status !== AppointmentSlotStatus::Available || $slot->starts_at->isPast()) {
                throw ValidationException::withMessages([
                    'appointment_slot_id' => ['The selected appointment slot is not available.'],
                ]);
            }

            $hospitalContext = $this->resolveHospitalContext($doctor, $data);
            $price = $this->resolvePrice($doctor, $hospitalContext);
            $status = $price > 0 ? AppointmentStatus::PendingPayment : AppointmentStatus::Confirmed;

            $appointment = Appointment::query()->create([
                'appointment_number' => $this->numberGenerator->generate(),
                'patient_user_id' => $patient->id,
                'doctor_profile_id' => $doctor->id,
                'provider_id' => $doctor->provider_id,
                'hospital_provider_id' => $hospitalContext['hospital_provider_id'] ?? null,
                'hospital_department_id' => $hospitalContext['hospital_department_id'] ?? null,
                'hospital_doctor_id' => $hospitalContext['hospital_doctor_id'] ?? null,
                'branch_id' => $slot->branch_id,
                'appointment_slot_id' => $slot->id,
                'consultation_type' => $data['consultation_type'],
                'problem_description' => $data['problem_description'] ?? null,
                'price' => $price,
                'currency' => 'EGP',
                'status' => $status,
                'booked_at' => now(),
                'confirmed_at' => $status === AppointmentStatus::Confirmed ? now() : null,
            ]);

            $slot->update([
                'status' => AppointmentSlotStatus::Booked,
                'hold_expires_at' => null,
            ]);

            $appointment->statusHistories()->create([
                'from_status' => null,
                'to_status' => $status->value,
                'actor_id' => $patient->id,
                'reason' => 'Appointment booked.',
                'metadata' => [
                    'slot_id' => $slot->id,
                    'price_source' => $hospitalContext['price_source'] ?? 'doctor_profile.consultation_fee',
                    'hospital_context' => $hospitalContext === null ? null : [
                        'hospital_provider_id' => $hospitalContext['hospital_provider_id'],
                        'hospital_department_id' => $hospitalContext['hospital_department_id'],
                        'hospital_doctor_id' => $hospitalContext['hospital_doctor_id'],
                    ],
                ],
            ]);

            $this->auditLogService->log('appointment.booked', $appointment, $patient, metadata: [
                'slot_id' => $slot->id,
                'status' => $status->value,
                'hospital_provider_id' => $hospitalContext['hospital_provider_id'] ?? null,
                'hospital_department_id' => $hospitalContext['hospital_department_id'] ?? null,
                'hospital_doctor_id' => $hospitalContext['hospital_doctor_id'] ?? null,
            ]);

            if ($status === AppointmentStatus::PendingPayment) {
                $this->paymentCreationService->createForAppointment($appointment, $patient);
            }

            return $appointment->load(['slot', 'doctorProfile.provider', 'provider', 'branch', 'hospital', 'hospitalDepartment', 'hospitalDoctorLink']);
        });
    }

    private function ensureDoctorIsBookable(DoctorProfile $doctor): void
    {
        if (
            $doctor->provider->type !== ProviderType::Doctor
            || $doctor->provider->status !== ProviderStatus::Approved
            || ! $doctor->provider->is_active
        ) {
            throw ValidationException::withMessages([
                'doctor_profile_id' => ['The selected doctor is not available for booking.'],
            ]);
        }
    }

    /**
     * @return array{hospital_provider_id:int,hospital_department_id:int,hospital_doctor_id:int,price_source:string}|null
     */
    private function resolveHospitalContext(DoctorProfile $doctor, array $data): ?array
    {
        $hasContext = filled($data['hospital_provider_id'] ?? null)
            || filled($data['hospital_department_id'] ?? null)
            || filled($data['hospital_doctor_id'] ?? null);

        if (! $hasContext) {
            return null;
        }

        if (
            ! filled($data['hospital_doctor_id'] ?? null)
            && (! filled($data['hospital_provider_id'] ?? null) || ! filled($data['hospital_department_id'] ?? null))
        ) {
            throw ValidationException::withMessages([
                'hospital_context' => $this->localizedValidationMessage(
                    '\u0627\u062E\u062A\u0631 \u0627\u0644\u0645\u0633\u062A\u0634\u0641\u0649 \u0648\u0627\u0644\u0642\u0633\u0645 \u0642\u0628\u0644 \u0625\u0643\u0645\u0627\u0644 \u0627\u0644\u062D\u062C\u0632 \u0645\u0646 \u062E\u0644\u0627\u0644 \u0645\u0633\u062A\u0634\u0641\u0649.',
                    'Hospital and department are required for hospital booking context.',
                ),
            ]);
        }

        $linkQuery = HospitalDoctor::query()
            ->with(['hospital', 'department', 'doctorProvider'])
            ->publiclyVisible()
            ->where('doctor_provider_id', $doctor->provider_id);

        if (filled($data['hospital_doctor_id'] ?? null)) {
            $linkQuery->whereKey($data['hospital_doctor_id']);
        }

        if (filled($data['hospital_provider_id'] ?? null)) {
            $linkQuery->where('hospital_provider_id', $data['hospital_provider_id']);
        }

        if (filled($data['hospital_department_id'] ?? null)) {
            $linkQuery->where('hospital_department_id', $data['hospital_department_id']);
        }

        $link = $linkQuery->first();

        if (! $link) {
            throw ValidationException::withMessages([
                'hospital_context' => $this->localizedValidationMessage(
                    '\u0644\u0627 \u064A\u0645\u0643\u0646 \u0627\u0633\u062A\u062E\u062F\u0627\u0645 \u0647\u0630\u0627 \u0627\u0644\u0645\u0633\u062A\u0634\u0641\u0649 \u0623\u0648 \u0627\u0644\u0642\u0633\u0645 \u0645\u0639 \u0627\u0644\u0637\u0628\u064A\u0628 \u0627\u0644\u0645\u062E\u062A\u0627\u0631.',
                    'Hospital context is not valid for the selected doctor.',
                ),
            ]);
        }

        if (
            ! $link->department
            || ! $link->department->is_active
            || (int) $link->department->hospital_provider_id !== (int) $link->hospital_provider_id
        ) {
            throw ValidationException::withMessages([
                'hospital_department_id' => $this->localizedValidationMessage(
                    '\u0627\u0644\u0642\u0633\u0645 \u0627\u0644\u0645\u062E\u062A\u0627\u0631 \u063A\u064A\u0631 \u0645\u062A\u0627\u062D \u0644\u0647\u0630\u0627 \u0627\u0644\u0645\u0633\u062A\u0634\u0641\u0649.',
                    'The selected department is not available for this hospital.',
                ),
            ]);
        }

        if (
            $link->hospital->type !== ProviderType::Hospital
            || $link->hospital->status !== ProviderStatus::Approved
            || ! $link->hospital->is_active
        ) {
            throw ValidationException::withMessages([
                'hospital_provider_id' => $this->localizedValidationMessage(
                    '\u0627\u0644\u0645\u0633\u062A\u0634\u0641\u0649 \u0627\u0644\u0645\u062E\u062A\u0627\u0631 \u063A\u064A\u0631 \u0645\u062A\u0627\u062D \u0644\u0644\u062D\u062C\u0632.',
                    'The selected hospital is not available for booking.',
                ),
            ]);
        }

        if (
            $link->doctorProvider->type !== ProviderType::Doctor
            || $link->doctorProvider->status !== ProviderStatus::Approved
            || ! $link->doctorProvider->is_active
            || (int) $link->doctorProvider->id !== (int) $doctor->provider_id
        ) {
            throw ValidationException::withMessages([
                'doctor_profile_id' => $this->localizedValidationMessage(
                    '\u0627\u0644\u0637\u0628\u064A\u0628 \u0627\u0644\u0645\u062E\u062A\u0627\u0631 \u063A\u064A\u0631 \u0645\u0631\u062A\u0628\u0637 \u0628\u0647\u0630\u0627 \u0627\u0644\u0645\u0633\u062A\u0634\u0641\u0649 \u0623\u0648 \u0627\u0644\u0642\u0633\u0645.',
                    'The selected doctor is not linked to this hospital department.',
                ),
            ]);
        }

        return [
            'hospital_provider_id' => (int) $link->hospital_provider_id,
            'hospital_department_id' => (int) $link->hospital_department_id,
            'hospital_doctor_id' => (int) $link->id,
            'price_source' => $link->consultation_fee !== null
                ? 'hospital_doctors.consultation_fee'
                : 'doctor_profile.consultation_fee',
        ];
    }

    private function resolvePrice(DoctorProfile $doctor, ?array $hospitalContext): float
    {
        if ($hospitalContext !== null) {
            $link = HospitalDoctor::query()->find($hospitalContext['hospital_doctor_id']);
            if ($link?->consultation_fee !== null) {
                return (float) $link->consultation_fee;
            }
        }

        return (float) ($doctor->consultation_fee ?? 0);
    }

    /**
     * Keep source files ASCII-safe on Windows while returning Arabic-first messages at runtime.
     *
     * @return array<int, string>
     */
    private function localizedValidationMessage(string $arabicUnicodeEscapes, string $english): array
    {
        return [
            json_decode('"'.$arabicUnicodeEscapes.'"', true, 512, JSON_THROW_ON_ERROR),
            $english,
        ];
    }
}
