<?php

namespace App\Modules\Appointments\Application\Services;

use App\Models\User;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use Illuminate\Support\Facades\DB;

class PatientAppointmentService
{
    public function __construct(private readonly AppointmentStatusService $statusService) {}

    public function cancel(User $patient, Appointment $appointment, ?string $reason = null): Appointment
    {
        return DB::transaction(function () use ($patient, $appointment, $reason): Appointment {
            $appointment = Appointment::query()
                ->whereKey($appointment->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->statusService->assertStatus($appointment, [
                AppointmentStatus::PendingPayment,
                AppointmentStatus::PendingPaymentReview,
                AppointmentStatus::Confirmed,
                AppointmentStatus::Accepted,
            ], 'This appointment cannot be cancelled in its current status.');

            $this->statusService->releaseSlotIfPresent($appointment);

            return $this->statusService->transition(
                $appointment,
                AppointmentStatus::CancelledByPatient,
                $patient,
                'appointment.cancelled_by_patient',
                $reason,
            );
        });
    }
}
