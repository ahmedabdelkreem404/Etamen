<?php

namespace App\Modules\Appointments\Application\Services;

use App\Models\User;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use Illuminate\Support\Facades\DB;

class AdminAppointmentService
{
    public function __construct(private readonly AppointmentStatusService $statusService) {}

    public function forceCancel(User $admin, Appointment $appointment, string $reason): Appointment
    {
        return DB::transaction(function () use ($admin, $appointment, $reason): Appointment {
            $appointment = Appointment::query()->whereKey($appointment->id)->lockForUpdate()->firstOrFail();

            $this->statusService->assertStatus($appointment, [
                AppointmentStatus::Draft,
                AppointmentStatus::PendingPayment,
                AppointmentStatus::PendingPaymentReview,
                AppointmentStatus::Confirmed,
                AppointmentStatus::Accepted,
            ], 'This appointment cannot be force-cancelled in its current status.');

            $this->statusService->releaseSlotIfPresent($appointment);

            return $this->statusService->transition(
                $appointment,
                AppointmentStatus::CancelledByDoctor,
                $admin,
                'admin.appointment.force_cancelled',
                $reason,
                ['forced_by_admin' => true],
            );
        });
    }
}
