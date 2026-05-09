<?php

namespace App\Modules\Fitness\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Fitness\Domain\Enums\CoachBookingStatus;
use App\Modules\Fitness\Infrastructure\Models\CoachBooking;

class CoachBookingStatusService
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function transition(
        CoachBooking $booking,
        CoachBookingStatus $to,
        ?User $actor,
        string $event,
        ?string $reason = null,
        array $metadata = [],
        array $extra = [],
    ): CoachBooking {
        $from = $booking->status;
        $before = $booking->getAttributes();

        $booking->forceFill([
            'status' => $to,
            ...$extra,
        ])->save();

        if ($from !== $to) {
            $booking->statusHistories()->create([
                'from_status' => $from?->value,
                'to_status' => $to,
                'changed_by' => $actor?->id,
                'reason' => $reason,
                'metadata' => $metadata,
            ]);
        }

        $this->auditLogService->log($event, $booking, $actor, before: $before, after: $booking->getAttributes(), metadata: $metadata);

        return $booking->refresh();
    }
}
