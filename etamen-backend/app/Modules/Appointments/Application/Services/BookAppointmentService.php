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

            $price = (float) ($doctor->consultation_fee ?? 0);
            $status = $price > 0 ? AppointmentStatus::PendingPayment : AppointmentStatus::Confirmed;

            $appointment = Appointment::query()->create([
                'appointment_number' => $this->numberGenerator->generate(),
                'patient_user_id' => $patient->id,
                'doctor_profile_id' => $doctor->id,
                'provider_id' => $doctor->provider_id,
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
                    'price_source' => 'doctor_profile.consultation_fee',
                ],
            ]);

            $this->auditLogService->log('appointment.booked', $appointment, $patient, metadata: [
                'slot_id' => $slot->id,
                'status' => $status->value,
            ]);

            if ($status === AppointmentStatus::PendingPayment) {
                $this->paymentCreationService->createForAppointment($appointment, $patient);
            }

            return $appointment->load(['slot', 'doctorProfile.provider', 'provider', 'branch']);
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
}
