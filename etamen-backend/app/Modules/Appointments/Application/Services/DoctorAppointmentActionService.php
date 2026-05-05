<?php

namespace App\Modules\Appointments\Application\Services;

use App\Models\User;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\Wallets\Application\Services\WalletPostingService;
use Illuminate\Support\Facades\DB;

class DoctorAppointmentActionService
{
    public function __construct(
        private readonly AppointmentStatusService $statusService,
        private readonly WalletPostingService $walletPostingService,
    ) {}

    public function accept(User $doctorUser, Appointment $appointment): Appointment
    {
        return $this->transitionWithLock(
            $doctorUser,
            $appointment,
            [AppointmentStatus::Confirmed],
            AppointmentStatus::Accepted,
            'doctor_appointment.accepted',
            'Only confirmed appointments can be accepted.',
        );
    }

    public function reject(User $doctorUser, Appointment $appointment, string $reason): Appointment
    {
        return DB::transaction(function () use ($doctorUser, $appointment, $reason): Appointment {
            $appointment = Appointment::query()->whereKey($appointment->id)->lockForUpdate()->firstOrFail();

            $this->statusService->assertStatus($appointment, [AppointmentStatus::Confirmed], 'Only confirmed appointments can be rejected.');
            $this->statusService->releaseSlotIfPresent($appointment);

            return $this->statusService->transition(
                $appointment,
                AppointmentStatus::Rejected,
                $doctorUser,
                'doctor_appointment.rejected',
                $reason,
            );
        });
    }

    public function complete(User $doctorUser, Appointment $appointment): Appointment
    {
        $appointment = $this->transitionWithLock(
            $doctorUser,
            $appointment,
            [AppointmentStatus::Accepted],
            AppointmentStatus::Completed,
            'appointment.completed',
            'Only accepted appointments can be completed.',
        );

        $this->walletPostingService->releaseAppointment($appointment, $doctorUser);

        return $appointment;
    }

    public function markNoShow(User $doctorUser, Appointment $appointment, ?string $reason = null): Appointment
    {
        return $this->transitionWithLock(
            $doctorUser,
            $appointment,
            [AppointmentStatus::Accepted],
            AppointmentStatus::NoShow,
            'appointment.no_show',
            'Only accepted appointments can be marked as no-show.',
            $reason,
        );
    }

    private function transitionWithLock(
        User $actor,
        Appointment $appointment,
        array $allowedFrom,
        AppointmentStatus $to,
        string $auditAction,
        string $invalidMessage,
        ?string $reason = null,
    ): Appointment {
        return DB::transaction(function () use ($actor, $appointment, $allowedFrom, $to, $auditAction, $invalidMessage, $reason): Appointment {
            $appointment = Appointment::query()->whereKey($appointment->id)->lockForUpdate()->firstOrFail();

            $this->statusService->assertStatus($appointment, $allowedFrom, $invalidMessage);

            return $this->statusService->transition($appointment, $to, $actor, $auditAction, $reason);
        });
    }
}
