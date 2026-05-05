<?php

namespace App\Modules\Pharmacies\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Payments\Application\Services\PaymentCreationService;
use App\Modules\Pharmacies\Domain\Enums\PharmacyDeliveryMethod;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderPaymentStatus;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderStatus;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyOrder;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyPrescription;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyProduct;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Domain\Enums\ServiceType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Wallets\Application\Services\CommissionService;
use App\Modules\Wallets\Application\Services\WalletPostingService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PharmacyOrderService
{
    public function __construct(
        private readonly PharmacyAccessService $accessService,
        private readonly PharmacyPrescriptionService $prescriptionService,
        private readonly PharmacyOrderNumberGenerator $numberGenerator,
        private readonly PharmacyOrderStatusService $statusService,
        private readonly PaymentCreationService $paymentCreationService,
        private readonly CommissionService $commissionService,
        private readonly WalletPostingService $walletPostingService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function create(User $patient, array $data): PharmacyOrder
    {
        if (! $patient->hasRole(UserRole::Patient->value)) {
            throw new AuthorizationException('Only patients can create pharmacy orders.');
        }

        return DB::transaction(function () use ($patient, $data): PharmacyOrder {
            $pharmacy = Provider::query()
                ->whereKey($data['pharmacy_provider_id'])
                ->where('type', ProviderType::Pharmacy)
                ->lockForUpdate()
                ->firstOrFail();

            $this->accessService->assertApprovedPharmacy($pharmacy);

            $prescription = null;
            if (! empty($data['prescription_id'])) {
                $prescription = PharmacyPrescription::query()->whereKey($data['prescription_id'])->firstOrFail();
                $this->prescriptionService->assertUsableForOrder($prescription, $patient, $pharmacy->id);
            }

            $items = $this->buildItems($data['items'], $pharmacy, $prescription);
            $subtotal = collect($items)->sum('line_total');
            $discountTotal = 0.0;
            $grandTotal = max(round($subtotal - $discountTotal, 2), 0);
            $commission = $this->commissionService->calculate(ProviderType::Pharmacy, ServiceType::PharmacyOrder, $grandTotal);

            $order = PharmacyOrder::query()->create([
                'order_number' => $this->numberGenerator->generate(),
                'patient_user_id' => $patient->id,
                'pharmacy_provider_id' => $pharmacy->id,
                'prescription_id' => $prescription?->id,
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'commission_amount' => $commission['commission_amount'],
                'provider_net_amount' => $commission['net_amount'],
                'grand_total' => $grandTotal,
                'currency' => 'EGP',
                'payment_status' => PharmacyOrderPaymentStatus::Unpaid,
                'order_status' => PharmacyOrderStatus::PharmacyReview,
                'delivery_method' => $data['delivery_method'] ?? PharmacyDeliveryMethod::Pickup->value,
                'delivery_address' => $data['delivery_address'] ?? null,
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
                'to_status' => PharmacyOrderStatus::PharmacyReview->value,
                'actor_id' => $patient->id,
                'reason' => 'Pharmacy order created.',
                'metadata' => ['items_count' => count($items)],
                'created_at' => now(),
            ]);

            $this->auditLogService->log('pharmacy_order.created', $order, $patient, metadata: [
                'pharmacy_provider_id' => $pharmacy->id,
                'grand_total' => $grandTotal,
            ]);

            return $order->refresh()->load(['items', 'pharmacy', 'prescription.uploadedFile']);
        });
    }

    public function createPayment(User $patient, PharmacyOrder $order): PharmacyOrder
    {
        if ((int) $order->patient_user_id !== (int) $patient->id) {
            throw new AuthorizationException('You cannot pay this pharmacy order.');
        }

        return DB::transaction(function () use ($patient, $order): PharmacyOrder {
            $order = PharmacyOrder::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->payment_id) {
                return $order->refresh()->load(['payment.paymentMethod', 'items']);
            }

            if (! in_array($order->order_status, [PharmacyOrderStatus::Accepted, PharmacyOrderStatus::AwaitingPayment], true)) {
                throw ValidationException::withMessages([
                    'order' => ['This pharmacy order must be accepted before payment.'],
                ]);
            }

            if ((float) $order->grand_total <= 0) {
                throw ValidationException::withMessages([
                    'order' => ['This pharmacy order has no payable amount.'],
                ]);
            }

            $payment = $this->paymentCreationService->createForPharmacyOrder($order, $patient);

            $order->forceFill([
                'payment_id' => $payment->id,
                'payment_status' => PharmacyOrderPaymentStatus::PendingPayment,
            ])->save();

            $this->statusService->transition(
                $order,
                PharmacyOrderStatus::AwaitingPayment,
                $patient,
                'pharmacy_order.awaiting_payment',
                'Payment created for pharmacy order.',
                ['payment_id' => $payment->id],
            );

            return $order->refresh()->load(['payment.paymentMethod', 'items']);
        });
    }

    public function providerUpdateStatus(User $providerUser, PharmacyOrder $order, PharmacyOrderStatus $to, ?string $reason = null): PharmacyOrder
    {
        return DB::transaction(function () use ($providerUser, $order, $to, $reason): PharmacyOrder {
            $provider = $this->accessService->currentPharmacyFor($providerUser);
            $order = PharmacyOrder::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            abort_if((int) $order->pharmacy_provider_id !== (int) $provider->id, 403);

            return $this->transitionProviderOrder($providerUser, $order, $to, $reason);
        });
    }

    public function adminUpdateStatus(User $admin, PharmacyOrder $order, PharmacyOrderStatus $to, ?string $reason = null): PharmacyOrder
    {
        return DB::transaction(function () use ($admin, $order, $to, $reason): PharmacyOrder {
            $order = PharmacyOrder::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            return $this->transitionProviderOrder($admin, $order, $to, $reason, admin: true);
        });
    }

    private function transitionProviderOrder(User $actor, PharmacyOrder $order, PharmacyOrderStatus $to, ?string $reason, bool $admin = false): PharmacyOrder
    {
        $this->assertTransitionAllowed($order, $to, $admin);

        if ($to === PharmacyOrderStatus::Accepted) {
            $this->reserveStockIfNeeded($order, $actor);
            $order->refresh();
        }

        $order = $this->statusService->transition(
            $order,
            $to,
            $actor,
            $admin ? 'admin.pharmacy_order.status_updated' : 'pharmacy_order.status_updated',
            $reason,
        );

        if ($to === PharmacyOrderStatus::Cancelled) {
            $this->releaseReservedStockIfNeeded($order, $actor);
            $order->refresh();
        }

        if ($to === PharmacyOrderStatus::Delivered) {
            $this->walletPostingService->releasePharmacyOrder($order, $actor);
        }

        return $order->load(['items', 'payment', 'prescription.uploadedFile']);
    }

    private function assertTransitionAllowed(PharmacyOrder $order, PharmacyOrderStatus $to, bool $admin): void
    {
        if ($order->order_status === $to) {
            return;
        }

        $allowed = match ($to) {
            PharmacyOrderStatus::Accepted => [PharmacyOrderStatus::Pending, PharmacyOrderStatus::PharmacyReview],
            PharmacyOrderStatus::Rejected => [PharmacyOrderStatus::Pending, PharmacyOrderStatus::PharmacyReview],
            PharmacyOrderStatus::Preparing => [PharmacyOrderStatus::Paid],
            PharmacyOrderStatus::ReadyForPickup => [PharmacyOrderStatus::Preparing, PharmacyOrderStatus::Paid],
            PharmacyOrderStatus::OutForDelivery => [PharmacyOrderStatus::Preparing, PharmacyOrderStatus::ReadyForPickup, PharmacyOrderStatus::Paid],
            PharmacyOrderStatus::Delivered => [PharmacyOrderStatus::Preparing, PharmacyOrderStatus::ReadyForPickup, PharmacyOrderStatus::OutForDelivery, PharmacyOrderStatus::Paid],
            PharmacyOrderStatus::Cancelled => $admin
                ? [PharmacyOrderStatus::Pending, PharmacyOrderStatus::PharmacyReview, PharmacyOrderStatus::Accepted, PharmacyOrderStatus::AwaitingPayment]
                : [PharmacyOrderStatus::Pending, PharmacyOrderStatus::PharmacyReview, PharmacyOrderStatus::Accepted, PharmacyOrderStatus::AwaitingPayment],
            default => [],
        };

        if (! in_array($order->order_status, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => ['This pharmacy order cannot move to the requested status.'],
            ]);
        }

        if (in_array($to, [PharmacyOrderStatus::Preparing, PharmacyOrderStatus::ReadyForPickup, PharmacyOrderStatus::OutForDelivery, PharmacyOrderStatus::Delivered], true)
            && $order->payment_status !== PharmacyOrderPaymentStatus::Paid) {
            throw ValidationException::withMessages([
                'payment_status' => ['This pharmacy order must be paid before fulfillment.'],
            ]);
        }

        if ($to === PharmacyOrderStatus::Cancelled && $order->payment_status === PharmacyOrderPaymentStatus::Paid) {
            throw ValidationException::withMessages([
                'payment_status' => ['Paid pharmacy orders need a controlled refund flow before cancellation.'],
            ]);
        }
    }

    private function reserveStockIfNeeded(PharmacyOrder $order, User $actor): void
    {
        $order = PharmacyOrder::query()->with('items')->whereKey($order->id)->lockForUpdate()->firstOrFail();

        if ($order->stock_reserved_at) {
            return;
        }

        foreach ($order->items as $item) {
            $product = PharmacyProduct::query()
                ->whereKey($item->product_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($product->stock_quantity < $item->quantity) {
                throw ValidationException::withMessages([
                    'stock' => ['One or more selected products no longer have enough stock.'],
                ]);
            }

            $product->decrement('stock_quantity', $item->quantity);
        }

        $metadata = $order->metadata ?? [];
        $metadata['stock_reserved'] = true;

        $before = $order->getAttributes();
        $order->forceFill([
            'stock_reserved_at' => now(),
            'metadata' => $metadata,
        ])->save();

        $this->auditLogService->log('pharmacy_order.stock_reserved', $order, $actor, before: $before, after: $order->getAttributes());
    }

    private function releaseReservedStockIfNeeded(PharmacyOrder $order, User $actor): void
    {
        $order = PharmacyOrder::query()->with('items')->whereKey($order->id)->lockForUpdate()->firstOrFail();

        if (! $order->stock_reserved_at || $order->stock_released_at || $order->order_status === PharmacyOrderStatus::Delivered) {
            return;
        }

        foreach ($order->items as $item) {
            PharmacyProduct::query()
                ->whereKey($item->product_id)
                ->lockForUpdate()
                ->firstOrFail()
                ->increment('stock_quantity', $item->quantity);
        }

        $metadata = $order->metadata ?? [];
        $metadata['stock_released'] = true;

        $before = $order->getAttributes();
        $order->forceFill([
            'stock_released_at' => now(),
            'metadata' => $metadata,
        ])->save();

        $this->auditLogService->log('pharmacy_order.stock_released', $order, $actor, before: $before, after: $order->getAttributes());
    }

    private function buildItems(array $requestedItems, Provider $pharmacy, ?PharmacyPrescription $prescription): array
    {
        $items = [];

        foreach ($requestedItems as $requestedItem) {
            $product = PharmacyProduct::query()
                ->whereKey($requestedItem['product_id'])
                ->where('provider_id', $pharmacy->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $product->is_active) {
                throw ValidationException::withMessages(['items' => ['One or more selected products are inactive.']]);
            }

            $quantity = (int) $requestedItem['quantity'];

            if ($product->stock_quantity < $quantity) {
                throw ValidationException::withMessages(['items' => ['One or more selected products do not have enough stock.']]);
            }

            if ($product->requires_prescription && ! $prescription) {
                throw ValidationException::withMessages([
                    'prescription_id' => ['A prescription is required for one or more selected products.'],
                ]);
            }

            $unitPrice = round((float) $product->price, 2);
            $lineTotal = round($unitPrice * $quantity, 2);

            $items[] = [
                'product_id' => $product->id,
                'product_name' => $product->name_en,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'line_total' => $lineTotal,
            ];
        }

        return $items;
    }
}
