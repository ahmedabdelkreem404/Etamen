<?php

namespace App\Modules\Radiology\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Radiology\Domain\Enums\RadiologyOrderStatus;
use App\Modules\Radiology\Infrastructure\Models\RadiologyOrder;

class RadiologyOrderStatusService
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function transition(
        RadiologyOrder $order,
        RadiologyOrderStatus $to,
        ?User $actor,
        string $event,
        ?string $reason = null,
        array $metadata = [],
        array $extra = [],
    ): RadiologyOrder {
        $from = $order->status;
        $before = $order->getAttributes();

        $order->forceFill([
            'status' => $to,
            ...$extra,
        ])->save();

        if ($from !== $to) {
            $order->statusHistories()->create([
                'from_status' => $from?->value,
                'to_status' => $to,
                'changed_by' => $actor?->id,
                'reason' => $reason,
                'metadata' => $metadata,
            ]);
        }

        $this->auditLogService->log($event, $order, $actor, before: $before, after: $order->getAttributes(), metadata: $metadata);

        return $order->refresh();
    }
}
