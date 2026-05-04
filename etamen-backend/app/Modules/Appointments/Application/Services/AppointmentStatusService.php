<?php

namespace App\Modules\Appointments\Application\Services;

use App\Models\User;
use App\Modules\Appointments\Domain\Enums\AppointmentSlotStatus;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use Illuminate\Validation\ValidationException;

class AppointmentStatusService
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function transition(
        Appointment $appointment,
        AppointmentStatus $to,
        ?User $actor,
        string $auditAction,
        ?string $reason = null,
        array $metadata = [],
    ): Appointment {
        $from = $appointment->status;
        $before = $appointment->getAttributes();
        $timestamps = $this->timestampsFor($to);

        $appointment->forceFill([
            'status' => $to,
            ...$timestamps,
        ])->save();

        $appointment->statusHistories()->create([
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'actor_id' => $actor?->id,
            'reason' => $reason,
            'metadata' => $metadata,
        ]);

        $this->auditLogService->log($auditAction, $appointment, $actor, before: $before, after: $appointment->getAttributes(), metadata: $metadata + ['reason' => $reason]);

        return $appointment->refresh();
    }

    public function assertStatus(Appointment $appointment, array $allowed, string $message): void
    {
        $allowedValues = array_map(fn (AppointmentStatus $status): string => $status->value, $allowed);

        if (! in_array($appointment->status->value, $allowedValues, true)) {
            throw ValidationException::withMessages(['status' => [$message]]);
        }
    }

    public function releaseSlotIfPresent(Appointment $appointment): void
    {
        if ($appointment->slot) {
            $appointment->slot->update([
                'status' => AppointmentSlotStatus::Available,
                'hold_expires_at' => null,
            ]);
        }
    }

    private function timestampsFor(AppointmentStatus $status): array
    {
        return match ($status) {
            AppointmentStatus::Confirmed => ['confirmed_at' => now()],
            AppointmentStatus::Accepted => ['accepted_at' => now()],
            AppointmentStatus::Rejected => ['rejected_at' => now()],
            AppointmentStatus::CancelledByPatient, AppointmentStatus::CancelledByDoctor => ['cancelled_at' => now()],
            AppointmentStatus::Completed => ['completed_at' => now()],
            AppointmentStatus::NoShow => ['no_show_at' => now()],
            default => [],
        };
    }
}
