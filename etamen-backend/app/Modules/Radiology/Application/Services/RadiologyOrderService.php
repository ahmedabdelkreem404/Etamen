<?php

namespace App\Modules\Radiology\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Payments\Application\Services\PaymentCreationService;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderBranch;
use App\Modules\Radiology\Domain\Enums\RadiologyOrderStatus;
use App\Modules\Radiology\Infrastructure\Models\RadiologyOrder;
use App\Modules\Radiology\Infrastructure\Models\RadiologyScan;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RadiologyOrderService
{
    public function __construct(
        private readonly RadiologyOrderNumberGenerator $numberGenerator,
        private readonly RadiologyOrderStatusService $statusService,
        private readonly PaymentCreationService $paymentCreationService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function create(User $patient, array $data): RadiologyOrder
    {
        if (! $patient->hasRole(UserRole::Patient->value)) {
            throw new AuthorizationException('Only patients can create radiology orders.');
        }

        return DB::transaction(function () use ($patient, $data): RadiologyOrder {
            $provider = Provider::query()
                ->whereKey($data['provider_id'])
                ->where('type', ProviderType::Radiology)
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertPublicRadiologyProvider($provider);
            $branch = $this->resolveBranch($provider, $data['branch_id'] ?? null);
            $items = $this->buildItems($data['scans'], $provider, $branch);
            $subtotal = round((float) collect($items)->sum('total_price'), 2);
            $discount = 0.0;
            $total = max(round($subtotal - $discount, 2), 0);
            $status = $total > 0 ? RadiologyOrderStatus::PendingPayment : RadiologyOrderStatus::Accepted;

            $order = RadiologyOrder::query()->create([
                'order_number' => $this->numberGenerator->generate(),
                'patient_user_id' => $patient->id,
                'provider_id' => $provider->id,
                'branch_id' => $branch?->id,
                'status' => $status,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'scheduled_at' => $data['scheduled_at'] ?? null,
                'patient_notes' => $data['patient_notes'] ?? null,
                'accepted_at' => $status === RadiologyOrderStatus::Accepted ? now() : null,
            ]);

            foreach ($items as $item) {
                $order->items()->create($item);
            }

            $order->statusHistories()->create([
                'from_status' => null,
                'to_status' => $status,
                'changed_by' => $patient->id,
                'reason' => 'Radiology order created.',
                'metadata' => ['items_count' => count($items)],
            ]);

            if ($total > 0) {
                $payment = $this->paymentCreationService->createForRadiologyOrder($order, $patient);
                $order->forceFill(['payment_id' => $payment->id])->save();
            }

            $this->auditLogService->log('radiology_order.created', $order, $patient, metadata: [
                'provider_id' => $provider->id,
                'total_amount' => $total,
            ]);

            return $order->refresh()->load(['items.scan.category', 'provider', 'branch', 'payment.paymentMethod', 'results.file']);
        });
    }

    public function cancelByPatient(User $patient, RadiologyOrder $order, ?string $reason = null): RadiologyOrder
    {
        if ((int) $order->patient_user_id !== (int) $patient->id) {
            throw new AuthorizationException('You cannot cancel this radiology order.');
        }

        return DB::transaction(function () use ($patient, $order, $reason): RadiologyOrder {
            $order = RadiologyOrder::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! in_array($order->status, [RadiologyOrderStatus::PendingPayment, RadiologyOrderStatus::PendingPaymentReview], true)) {
                throw ValidationException::withMessages([
                    'status' => ['This radiology order cannot be cancelled by the patient from its current status.'],
                ]);
            }

            return $this->statusService->transition(
                $order,
                RadiologyOrderStatus::CancelledByPatient,
                $patient,
                'radiology_order.cancelled_by_patient',
                $reason,
                [],
                ['cancelled_at' => now()],
            )->load(['items', 'provider', 'branch', 'payment.paymentMethod', 'results.file']);
        });
    }

    public function accept(User $actor, RadiologyOrder $order): RadiologyOrder
    {
        return $this->transitionProviderOrder(
            $actor,
            $order,
            RadiologyOrderStatus::Accepted,
            [RadiologyOrderStatus::Paid],
            'radiology_order.accepted',
            'Radiology order accepted.',
            ['accepted_at' => now()],
        );
    }

    public function reject(User $actor, RadiologyOrder $order, ?string $reason = null): RadiologyOrder
    {
        return $this->transitionProviderOrder(
            $actor,
            $order,
            RadiologyOrderStatus::Rejected,
            [RadiologyOrderStatus::PendingPayment, RadiologyOrderStatus::Accepted],
            'radiology_order.rejected',
            $reason,
            ['rejected_at' => now()],
        );
    }

    public function start(User $actor, RadiologyOrder $order): RadiologyOrder
    {
        return $this->transitionProviderOrder(
            $actor,
            $order,
            RadiologyOrderStatus::InProgress,
            [RadiologyOrderStatus::Accepted],
            'radiology_order.started',
            'Radiology order started.',
        );
    }

    public function markResultReady(User $actor, RadiologyOrder $order): RadiologyOrder
    {
        return DB::transaction(function () use ($actor, $order): RadiologyOrder {
            $order = RadiologyOrder::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! in_array($order->status, [RadiologyOrderStatus::Accepted, RadiologyOrderStatus::InProgress, RadiologyOrderStatus::ResultReady], true)) {
                throw ValidationException::withMessages([
                    'status' => ['This radiology order cannot be marked result-ready from its current status.'],
                ]);
            }

            if (! $order->results()->where('is_visible_to_patient', true)->exists()) {
                throw ValidationException::withMessages([
                    'result' => ['At least one patient-visible result is required before marking result ready.'],
                ]);
            }

            return $this->statusService->transition(
                $order,
                RadiologyOrderStatus::ResultReady,
                $actor,
                'radiology_order.result_ready',
                'Radiology result is ready.',
            )->load(['items', 'provider', 'branch', 'payment.paymentMethod', 'results.file']);
        });
    }

    public function complete(User $actor, RadiologyOrder $order): RadiologyOrder
    {
        return $this->transitionProviderOrder(
            $actor,
            $order,
            RadiologyOrderStatus::Completed,
            [RadiologyOrderStatus::ResultReady],
            'radiology_order.completed',
            'Radiology order completed.',
            ['completed_at' => now()],
        );
    }

    public function forceCancel(User $admin, RadiologyOrder $order, ?string $reason = null): RadiologyOrder
    {
        return DB::transaction(function () use ($admin, $order, $reason): RadiologyOrder {
            $order = RadiologyOrder::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->status === RadiologyOrderStatus::Completed) {
                throw ValidationException::withMessages([
                    'status' => ['Completed radiology orders cannot be force-cancelled.'],
                ]);
            }

            return $this->statusService->transition(
                $order,
                RadiologyOrderStatus::CancelledByProvider,
                $admin,
                'admin.radiology_order.force_cancelled',
                $reason,
                [],
                ['cancelled_at' => now()],
            )->load(['items', 'provider', 'branch', 'payment.paymentMethod', 'results.file']);
        });
    }

    public function markPaidAfterPayment(RadiologyOrder $order, ?User $actor, array $metadata = []): RadiologyOrder
    {
        return DB::transaction(function () use ($order, $actor, $metadata): RadiologyOrder {
            $order = RadiologyOrder::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->status === RadiologyOrderStatus::Paid) {
                return $order->refresh();
            }

            if (! in_array($order->status, [RadiologyOrderStatus::PendingPayment, RadiologyOrderStatus::PendingPaymentReview], true)) {
                throw ValidationException::withMessages([
                    'status' => ['This radiology order cannot be marked paid from its current status.'],
                ]);
            }

            return $this->statusService->transition(
                $order,
                RadiologyOrderStatus::Paid,
                $actor,
                'radiology_order.paid_after_payment',
                'Payment verified.',
                $metadata,
            );
        });
    }

    public function moveToPaymentReview(RadiologyOrder $order, User $actor, array $metadata = []): RadiologyOrder
    {
        if ($order->status === RadiologyOrderStatus::PendingPaymentReview) {
            return $order->refresh();
        }

        if ($order->status !== RadiologyOrderStatus::PendingPayment) {
            throw ValidationException::withMessages([
                'status' => ['This radiology order cannot move to payment review.'],
            ]);
        }

        return $this->statusService->transition(
            $order,
            RadiologyOrderStatus::PendingPaymentReview,
            $actor,
            'radiology_order.pending_payment_review',
            'Manual payment proof uploaded.',
            $metadata,
        );
    }

    public function returnToPendingPayment(RadiologyOrder $order, User $actor, string $reason): RadiologyOrder
    {
        if ($order->status !== RadiologyOrderStatus::PendingPaymentReview) {
            return $order->refresh();
        }

        return $this->statusService->transition(
            $order,
            RadiologyOrderStatus::PendingPayment,
            $actor,
            'radiology_order.payment_review_rejected',
            $reason,
        );
    }

    private function transitionProviderOrder(
        User $actor,
        RadiologyOrder $order,
        RadiologyOrderStatus $to,
        array $allowedFrom,
        string $event,
        ?string $reason = null,
        array $extra = [],
    ): RadiologyOrder {
        return DB::transaction(function () use ($actor, $order, $to, $allowedFrom, $event, $reason, $extra): RadiologyOrder {
            $order = RadiologyOrder::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! in_array($order->status, $allowedFrom, true) && $order->status !== $to) {
                throw ValidationException::withMessages([
                    'status' => ['This radiology order cannot move to the requested status.'],
                ]);
            }

            return $this->statusService->transition($order, $to, $actor, $event, $reason, [], $extra)
                ->load(['items', 'provider', 'branch', 'payment.paymentMethod', 'results.file']);
        });
    }

    private function assertPublicRadiologyProvider(Provider $provider): void
    {
        if (
            $provider->type !== ProviderType::Radiology
            || $provider->status !== ProviderStatus::Approved
            || ! $provider->is_active
        ) {
            throw ValidationException::withMessages([
                'provider_id' => ['The selected radiology provider is not available.'],
            ]);
        }
    }

    private function resolveBranch(Provider $provider, ?int $branchId): ?ProviderBranch
    {
        if (! $branchId) {
            return null;
        }

        $branch = ProviderBranch::query()
            ->whereKey($branchId)
            ->where('provider_id', $provider->id)
            ->where('is_active', true)
            ->first();

        if (! $branch) {
            throw ValidationException::withMessages([
                'branch_id' => ['The selected branch does not belong to this radiology provider.'],
            ]);
        }

        return $branch;
    }

    private function buildItems(array $requestedScans, Provider $provider, ?ProviderBranch $branch): array
    {
        $items = [];

        foreach ($requestedScans as $requestedScan) {
            $quantity = (int) ($requestedScan['quantity'] ?? 1);
            $scan = RadiologyScan::query()
                ->with('category')
                ->whereKey($requestedScan['radiology_scan_id'])
                ->where('provider_id', $provider->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $scan->is_active || ! $scan->category?->is_active) {
                throw ValidationException::withMessages([
                    'scans' => ['One or more selected radiology scans are inactive.'],
                ]);
            }

            if ($branch && $scan->branch_id && (int) $scan->branch_id !== (int) $branch->id) {
                throw ValidationException::withMessages([
                    'branch_id' => ['One or more selected scans are not available at this branch.'],
                ]);
            }

            $unitPrice = round((float) ($scan->base_price ?? 0), 2);

            $items[] = [
                'radiology_scan_id' => $scan->id,
                'scan_name_ar' => $scan->name_ar,
                'scan_name_en' => $scan->name_en,
                'category_name_ar' => $scan->category?->name_ar,
                'category_name_en' => $scan->category?->name_en,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'total_price' => round($unitPrice * $quantity, 2),
                'preparation_snapshot_ar' => $scan->preparation_ar,
                'preparation_snapshot_en' => $scan->preparation_en,
            ];
        }

        return $items;
    }
}
