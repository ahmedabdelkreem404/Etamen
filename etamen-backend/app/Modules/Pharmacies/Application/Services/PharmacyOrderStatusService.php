<?php

namespace App\Modules\Pharmacies\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderStatus;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyOrder;
use Illuminate\Validation\ValidationException;

class PharmacyOrderStatusService
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function transition(
        PharmacyOrder $order,
        PharmacyOrderStatus $to,
        ?User $actor,
        string $auditAction,
        ?string $reason = null,
        array $metadata = [],
    ): PharmacyOrder {
        $from = $order->order_status;
        $before = $order->getAttributes();

        if ($from === $to) {
            return $order->refresh();
        }

        $order->forceFill([
            'order_status' => $to,
            ...$this->timestampsFor($to),
        ])->save();

        $order->statusHistories()->create([
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'actor_id' => $actor?->id,
            'reason' => $reason,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);

        $this->auditLogService->log($auditAction, $order, $actor, before: $before, after: $order->getAttributes(), metadata: $metadata + ['reason' => $reason]);

        return $order->refresh();
    }

    public function assertStatus(PharmacyOrder $order, array $allowed, string $message): void
    {
        if (! in_array($order->order_status, $allowed, true)) {
            throw ValidationException::withMessages(['status' => [$message]]);
        }
    }

    private function timestampsFor(PharmacyOrderStatus $status): array
    {
        return match ($status) {
            PharmacyOrderStatus::Accepted => ['accepted_at' => now()],
            PharmacyOrderStatus::Rejected => ['rejected_at' => now()],
            PharmacyOrderStatus::Delivered => ['delivered_at' => now()],
            PharmacyOrderStatus::Cancelled => ['cancelled_at' => now()],
            default => [],
        };
    }
}
