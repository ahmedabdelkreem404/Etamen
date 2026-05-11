<?php

namespace App\Modules\Labs\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Labs\Domain\Enums\LabOrderItemType;
use App\Modules\Labs\Domain\Enums\LabOrderPaymentStatus;
use App\Modules\Labs\Domain\Enums\LabOrderStatus;
use App\Modules\Labs\Infrastructure\Models\LabOrder;
use App\Modules\Labs\Infrastructure\Models\LabPackage;
use App\Modules\Labs\Infrastructure\Models\LabTest;
use App\Modules\Payments\Application\Services\PaymentCreationService;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Domain\Enums\ServiceType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Wallets\Application\Services\CommissionService;
use App\Modules\Wallets\Application\Services\WalletPostingService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LabOrderService
{
    public function __construct(
        private readonly LabAccessService $accessService,
        private readonly LabOrderNumberGenerator $numberGenerator,
        private readonly LabOrderStatusService $statusService,
        private readonly PaymentCreationService $paymentCreationService,
        private readonly CommissionService $commissionService,
        private readonly WalletPostingService $walletPostingService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function create(User $patient, array $data): LabOrder
    {
        if (! $patient->hasRole(UserRole::Patient->value)) {
            throw new AuthorizationException('Only patients can create lab orders.');
        }

        return DB::transaction(function () use ($patient, $data): LabOrder {
            $lab = Provider::query()
                ->whereKey($data['lab_provider_id'])
                ->where('type', ProviderType::Lab)
                ->lockForUpdate()
                ->firstOrFail();

            $this->accessService->assertApprovedLab($lab);

            $items = $this->buildItems($data['items'], $lab);
            $subtotal = collect($items)->sum('line_total');
            $discountTotal = 0.0;
            $grandTotal = max(round($subtotal - $discountTotal, 2), 0);
            $commission = $this->commissionService->calculate(ProviderType::Lab, ServiceType::LabOrder, $grandTotal);

            $order = LabOrder::query()->create([
                'order_number' => $this->numberGenerator->generate(),
                'patient_user_id' => $patient->id,
                'lab_provider_id' => $lab->id,
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'commission_amount' => $commission['commission_amount'],
                'provider_net_amount' => $commission['net_amount'],
                'grand_total' => $grandTotal,
                'currency' => 'EGP',
                'payment_status' => LabOrderPaymentStatus::Unpaid,
                'order_status' => LabOrderStatus::LabReview,
                'sample_collection_method' => $data['sample_collection_method'],
                'collection_address' => $data['collection_address'] ?? null,
                'scheduled_at' => $data['scheduled_at'] ?? null,
                'notes' => $data['notes'] ?? null,
                'metadata' => [
                    'commission_rule_id' => $commission['rule_id'],
                    'missing_commission_rule' => $commission['missing_rule'],
                ],
            ]);

            foreach ($items as $item) {
                $order->items()->create($item);
            }

            $order->statusHistories()->create([
                'from_status' => null,
                'to_status' => LabOrderStatus::LabReview->value,
                'actor_id' => $patient->id,
                'reason' => 'Lab order created.',
                'metadata' => ['items_count' => count($items)],
                'created_at' => now(),
            ]);

            $this->auditLogService->log('lab_order.created', $order, $patient, metadata: [
                'lab_provider_id' => $lab->id,
                'grand_total' => $grandTotal,
            ]);

            return $order->refresh()->load(['items', 'lab']);
        });
    }

    public function createPayment(User $patient, LabOrder $order): LabOrder
    {
        if ((int) $order->patient_user_id !== (int) $patient->id) {
            throw new AuthorizationException('You cannot pay this lab order.');
        }

        return DB::transaction(function () use ($patient, $order): LabOrder {
            $order = LabOrder::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->payment_id) {
                return $order->refresh()->load(['payment.paymentMethod', 'items']);
            }

            if (! in_array($order->order_status, [LabOrderStatus::Accepted, LabOrderStatus::AwaitingPayment], true)) {
                throw ValidationException::withMessages([
                    'order' => ['This lab order must be accepted before payment.'],
                ]);
            }

            if ((float) $order->grand_total <= 0) {
                throw ValidationException::withMessages([
                    'order' => ['This lab order has no payable amount.'],
                ]);
            }

            $payment = $this->paymentCreationService->createForLabOrder($order, $patient);

            $order->forceFill([
                'payment_id' => $payment->id,
                'payment_status' => LabOrderPaymentStatus::PendingPayment,
            ])->save();

            $this->statusService->transition(
                $order,
                LabOrderStatus::AwaitingPayment,
                $patient,
                'lab_order.awaiting_payment',
                'Payment created for lab order.',
                ['payment_id' => $payment->id],
            );

            return $order->refresh()->load(['payment.paymentMethod', 'items']);
        });
    }

    public function patientCancel(User $patient, LabOrder $order, ?string $reason = null): LabOrder
    {
        if ((int) $order->patient_user_id !== (int) $patient->id) {
            throw new AuthorizationException('You cannot cancel this lab order.');
        }

        return DB::transaction(function () use ($patient, $order, $reason): LabOrder {
            $order = LabOrder::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->payment_id || $order->payment_status !== LabOrderPaymentStatus::Unpaid) {
                throw ValidationException::withMessages([
                    'payment_status' => ['This lab order already entered the payment flow. Please use support or refund flow.'],
                ]);
            }

            if (! in_array($order->order_status, [
                LabOrderStatus::LabReview,
                LabOrderStatus::Accepted,
                LabOrderStatus::AwaitingPayment,
            ], true)) {
                throw ValidationException::withMessages([
                    'status' => ['This lab order cannot be cancelled by the patient now.'],
                ]);
            }

            $order = $this->statusService->transition(
                $order,
                LabOrderStatus::Cancelled,
                $patient,
                'lab_order.cancelled_by_patient',
                $reason ?: 'Patient cancelled before payment.',
            );

            return $order->refresh()->load(['items', 'lab', 'payment', 'results.file']);
        });
    }

    public function providerUpdateStatus(User $providerUser, LabOrder $order, LabOrderStatus $to, ?string $reason = null): LabOrder
    {
        return DB::transaction(function () use ($providerUser, $order, $to, $reason): LabOrder {
            $provider = $this->accessService->currentLabFor($providerUser);
            $order = LabOrder::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            abort_if((int) $order->lab_provider_id !== (int) $provider->id, 403);

            return $this->transitionProviderOrder($providerUser, $order, $to, $reason);
        });
    }

    public function providerWorkspaceUpdateStatus(User $providerUser, Provider $provider, LabOrder $order, LabOrderStatus $to, ?string $reason = null): LabOrder
    {
        return DB::transaction(function () use ($providerUser, $provider, $order, $to, $reason): LabOrder {
            abort_if($provider->type !== ProviderType::Lab, 403);

            $order = LabOrder::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            abort_if((int) $order->lab_provider_id !== (int) $provider->id, 403);

            return $this->transitionProviderOrder($providerUser, $order, $to, $reason);
        });
    }

    public function adminUpdateStatus(User $admin, LabOrder $order, LabOrderStatus $to, ?string $reason = null): LabOrder
    {
        return DB::transaction(function () use ($admin, $order, $to, $reason): LabOrder {
            $order = LabOrder::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            return $this->transitionProviderOrder($admin, $order, $to, $reason, admin: true);
        });
    }

    private function transitionProviderOrder(User $actor, LabOrder $order, LabOrderStatus $to, ?string $reason, bool $admin = false): LabOrder
    {
        $this->assertTransitionAllowed($order, $to, $admin);

        $order = $this->statusService->transition(
            $order,
            $to,
            $actor,
            $admin ? 'admin.lab_order.status_updated' : 'lab_order.status_updated',
            $reason,
        );

        if ($to === LabOrderStatus::Completed) {
            $this->walletPostingService->releaseLabOrder($order, $actor);
        }

        return $order->load(['items', 'payment', 'results.file']);
    }

    private function assertTransitionAllowed(LabOrder $order, LabOrderStatus $to, bool $admin): void
    {
        if ($order->order_status === $to) {
            return;
        }

        $allowed = match ($to) {
            LabOrderStatus::Accepted => [LabOrderStatus::LabReview],
            LabOrderStatus::Rejected => [LabOrderStatus::LabReview, LabOrderStatus::Accepted],
            LabOrderStatus::SampleScheduled => [LabOrderStatus::Paid],
            LabOrderStatus::SampleCollected => [LabOrderStatus::Paid, LabOrderStatus::SampleScheduled],
            LabOrderStatus::Processing => [LabOrderStatus::Paid, LabOrderStatus::SampleScheduled, LabOrderStatus::SampleCollected],
            LabOrderStatus::ResultReady => [LabOrderStatus::Paid, LabOrderStatus::SampleCollected, LabOrderStatus::Processing],
            LabOrderStatus::Completed => [LabOrderStatus::ResultReady],
            LabOrderStatus::Cancelled => $admin
                ? [LabOrderStatus::LabReview, LabOrderStatus::Accepted, LabOrderStatus::AwaitingPayment]
                : [LabOrderStatus::LabReview, LabOrderStatus::Accepted, LabOrderStatus::AwaitingPayment],
            default => [],
        };

        if (! in_array($order->order_status, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => ['This lab order cannot move to the requested status.'],
            ]);
        }

        if (in_array($to, [
            LabOrderStatus::SampleScheduled,
            LabOrderStatus::SampleCollected,
            LabOrderStatus::Processing,
            LabOrderStatus::ResultReady,
            LabOrderStatus::Completed,
        ], true) && $order->payment_status !== LabOrderPaymentStatus::Paid) {
            throw ValidationException::withMessages([
                'payment_status' => ['This lab order must be paid before fulfillment.'],
            ]);
        }

        if ($to === LabOrderStatus::Cancelled && $order->payment_status === LabOrderPaymentStatus::Paid) {
            throw ValidationException::withMessages([
                'payment_status' => ['Paid lab orders need a controlled refund flow before cancellation.'],
            ]);
        }
    }

    private function buildItems(array $requestedItems, Provider $lab): array
    {
        $items = [];

        foreach ($requestedItems as $requestedItem) {
            $type = LabOrderItemType::from($requestedItem['item_type']);
            $quantity = (int) $requestedItem['quantity'];

            if ($type === LabOrderItemType::Test) {
                $test = LabTest::query()
                    ->whereKey($requestedItem['test_id'] ?? null)
                    ->where('provider_id', $lab->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $test->is_active) {
                    throw ValidationException::withMessages(['items' => ['One or more selected lab tests are inactive.']]);
                }

                $unitPrice = round((float) $test->price, 2);
                $items[] = [
                    'item_type' => LabOrderItemType::Test,
                    'test_id' => $test->id,
                    'package_id' => null,
                    'item_name' => $test->name_en,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'line_total' => round($unitPrice * $quantity, 2),
                ];

                continue;
            }

            $package = LabPackage::query()
                ->whereKey($requestedItem['package_id'] ?? null)
                ->where('provider_id', $lab->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $package->is_active) {
                throw ValidationException::withMessages(['items' => ['One or more selected lab packages are inactive.']]);
            }

            $unitPrice = round((float) $package->price, 2);
            $items[] = [
                'item_type' => LabOrderItemType::Package,
                'test_id' => null,
                'package_id' => $package->id,
                'item_name' => $package->name_en,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'line_total' => round($unitPrice * $quantity, 2),
            ];
        }

        return $items;
    }
}
