<?php

namespace App\Modules\Labs\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Labs\Domain\Enums\LabOrderStatus;
use App\Modules\Labs\Infrastructure\Models\LabOrder;
use Illuminate\Validation\ValidationException;

class LabOrderStatusService
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function transition(
        LabOrder $order,
        LabOrderStatus $to,
        ?User $actor,
        string $auditAction,
        ?string $reason = null,
        array $metadata = [],
    ): LabOrder {
        $from = $order->order_status;

        if ($from === $to) {
            return $order->refresh();
        }

        $before = $order->getAttributes();
        $updates = ['order_status' => $to];

        match ($to) {
            LabOrderStatus::Accepted => $updates['accepted_at'] = now(),
            LabOrderStatus::Rejected => $updates['rejected_at'] = now(),
            LabOrderStatus::Paid => $updates['paid_at'] = $order->paid_at ?? now(),
            LabOrderStatus::SampleCollected => $updates['sample_collected_at'] = now(),
            LabOrderStatus::ResultReady => $updates['result_ready_at'] = now(),
            LabOrderStatus::Completed => $updates['completed_at'] = now(),
            LabOrderStatus::Cancelled => $updates['cancelled_at'] = now(),
            default => null,
        };

        $order->forceFill($updates)->save();

        $order->statusHistories()->create([
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'actor_id' => $actor?->id,
            'reason' => $reason,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);

        $this->auditLogService->log($auditAction, $order, $actor, before: $before, after: $order->getAttributes(), metadata: $metadata + [
            'from_status' => $from?->value,
            'to_status' => $to->value,
        ]);

        return $order->refresh();
    }

    public function assertStatus(LabOrder $order, array $allowed, string $message): void
    {
        if (! in_array($order->order_status, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => [$message],
            ]);
        }
    }
}
