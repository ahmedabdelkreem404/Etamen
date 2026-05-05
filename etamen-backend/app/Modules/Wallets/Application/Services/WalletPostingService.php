<?php

namespace App\Modules\Wallets\Application\Services;

use App\Models\User;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Labs\Domain\Enums\LabOrderPaymentStatus;
use App\Modules\Labs\Domain\Enums\LabOrderStatus;
use App\Modules\Labs\Infrastructure\Models\LabOrder;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderPaymentStatus;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderStatus;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyOrder;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Domain\Enums\ServiceType;
use App\Modules\Wallets\Domain\Enums\WalletTransactionStatus;
use App\Modules\Wallets\Domain\Enums\WalletTransactionType;
use App\Modules\Wallets\Infrastructure\Models\Wallet;
use App\Modules\Wallets\Infrastructure\Models\WalletTransaction;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class WalletPostingService
{
    public function __construct(
        private readonly WalletService $walletService,
        private readonly CommissionService $commissionService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function postVerifiedPayment(Payment $payment, ?User $actor = null): void
    {
        DB::transaction(function () use ($payment, $actor): void {
            $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($payment->status !== PaymentStatus::Verified) {
                return;
            }

            if ($payment->payable_type === PharmacyOrder::class) {
                $this->postVerifiedPharmacyOrderPayment($payment, $actor);

                return;
            }

            if ($payment->payable_type === LabOrder::class) {
                $this->postVerifiedLabOrderPayment($payment, $actor);

                return;
            }

            if ($payment->payable_type !== Appointment::class) {
                return;
            }

            $appointment = Appointment::query()->with('provider')->whereKey($payment->payable_id)->firstOrFail();

            if ($appointment->provider->type !== ProviderType::Doctor) {
                return;
            }

            $wallet = $this->walletService->walletForProvider($appointment->provider, $payment->currency);
            $commission = $this->commissionService->calculate(ProviderType::Doctor, ServiceType::Appointment, (float) $payment->amount);

            $hold = $this->createTransaction($wallet, [
                'source_type' => Payment::class,
                'source_id' => $payment->id,
                'type' => WalletTransactionType::Hold,
                'gross_amount' => $commission['gross_amount'],
                'commission_amount' => $commission['commission_amount'],
                'net_amount' => $commission['net_amount'],
                'status' => WalletTransactionStatus::Posted,
                'description' => 'Doctor appointment earning held after verified payment.',
                'metadata' => [
                    'appointment_id' => $appointment->id,
                    'commission_rule_id' => $commission['rule_id'],
                    'missing_commission_rule' => $commission['missing_rule'],
                ],
                'created_by' => $actor?->id,
                'idempotency_key' => 'payment:'.$payment->id.':provider_hold',
            ]);

            $this->createTransaction($wallet, [
                'source_type' => Payment::class,
                'source_id' => $payment->id,
                'type' => WalletTransactionType::Commission,
                'gross_amount' => $commission['gross_amount'],
                'commission_amount' => $commission['commission_amount'],
                'net_amount' => 0,
                'status' => WalletTransactionStatus::Posted,
                'description' => 'Platform commission recorded for doctor appointment.',
                'metadata' => [
                    'appointment_id' => $appointment->id,
                    'commission_rule_id' => $commission['rule_id'],
                    'missing_commission_rule' => $commission['missing_rule'],
                ],
                'created_by' => $actor?->id,
                'idempotency_key' => 'payment:'.$payment->id.':platform_commission',
            ]);

            $this->auditLogService->log('wallet.payment_hold_posted', $hold, $actor, metadata: [
                'payment_id' => $payment->id,
                'appointment_id' => $appointment->id,
                'wallet_id' => $wallet->id,
            ]);
        });
    }

    private function postVerifiedPharmacyOrderPayment(Payment $payment, ?User $actor = null): void
    {
        $order = PharmacyOrder::query()->with('pharmacy')->whereKey($payment->payable_id)->firstOrFail();

        if ($order->payment_status !== PharmacyOrderPaymentStatus::Paid || $order->pharmacy->type !== ProviderType::Pharmacy) {
            return;
        }

        $wallet = $this->walletService->walletForProvider($order->pharmacy, $payment->currency);
        $commission = $this->commissionService->calculate(ProviderType::Pharmacy, ServiceType::PharmacyOrder, (float) $payment->amount);

        $hold = $this->createTransaction($wallet, [
            'source_type' => Payment::class,
            'source_id' => $payment->id,
            'type' => WalletTransactionType::Hold,
            'gross_amount' => $commission['gross_amount'],
            'commission_amount' => $commission['commission_amount'],
            'net_amount' => $commission['net_amount'],
            'status' => WalletTransactionStatus::Posted,
            'description' => 'Pharmacy order earning held after verified payment.',
            'metadata' => [
                'pharmacy_order_id' => $order->id,
                'commission_rule_id' => $commission['rule_id'],
                'missing_commission_rule' => $commission['missing_rule'],
            ],
            'created_by' => $actor?->id,
            'idempotency_key' => 'payment:'.$payment->id.':provider_hold',
        ]);

        $this->createTransaction($wallet, [
            'source_type' => Payment::class,
            'source_id' => $payment->id,
            'type' => WalletTransactionType::Commission,
            'gross_amount' => $commission['gross_amount'],
            'commission_amount' => $commission['commission_amount'],
            'net_amount' => 0,
            'status' => WalletTransactionStatus::Posted,
            'description' => 'Platform commission recorded for pharmacy order.',
            'metadata' => [
                'pharmacy_order_id' => $order->id,
                'commission_rule_id' => $commission['rule_id'],
                'missing_commission_rule' => $commission['missing_rule'],
            ],
            'created_by' => $actor?->id,
            'idempotency_key' => 'payment:'.$payment->id.':platform_commission',
        ]);

        $this->auditLogService->log('wallet.pharmacy_order_hold_posted', $hold, $actor, metadata: [
            'payment_id' => $payment->id,
            'pharmacy_order_id' => $order->id,
            'wallet_id' => $wallet->id,
        ]);
    }

    private function postVerifiedLabOrderPayment(Payment $payment, ?User $actor = null): void
    {
        $order = LabOrder::query()->with('lab')->whereKey($payment->payable_id)->firstOrFail();

        if ($order->payment_status !== LabOrderPaymentStatus::Paid || $order->lab->type !== ProviderType::Lab) {
            return;
        }

        $wallet = $this->walletService->walletForProvider($order->lab, $payment->currency);
        $commission = $this->commissionService->calculate(ProviderType::Lab, ServiceType::LabOrder, (float) $payment->amount);

        $hold = $this->createTransaction($wallet, [
            'source_type' => Payment::class,
            'source_id' => $payment->id,
            'type' => WalletTransactionType::Hold,
            'gross_amount' => $commission['gross_amount'],
            'commission_amount' => $commission['commission_amount'],
            'net_amount' => $commission['net_amount'],
            'status' => WalletTransactionStatus::Posted,
            'description' => 'Lab order earning held after verified payment.',
            'metadata' => [
                'lab_order_id' => $order->id,
                'commission_rule_id' => $commission['rule_id'],
                'missing_commission_rule' => $commission['missing_rule'],
            ],
            'created_by' => $actor?->id,
            'idempotency_key' => 'payment:'.$payment->id.':provider_hold',
        ]);

        $this->createTransaction($wallet, [
            'source_type' => Payment::class,
            'source_id' => $payment->id,
            'type' => WalletTransactionType::Commission,
            'gross_amount' => $commission['gross_amount'],
            'commission_amount' => $commission['commission_amount'],
            'net_amount' => 0,
            'status' => WalletTransactionStatus::Posted,
            'description' => 'Platform commission recorded for lab order.',
            'metadata' => [
                'lab_order_id' => $order->id,
                'commission_rule_id' => $commission['rule_id'],
                'missing_commission_rule' => $commission['missing_rule'],
            ],
            'created_by' => $actor?->id,
            'idempotency_key' => 'payment:'.$payment->id.':platform_commission',
        ]);

        $this->auditLogService->log('wallet.lab_order_hold_posted', $hold, $actor, metadata: [
            'payment_id' => $payment->id,
            'lab_order_id' => $order->id,
            'wallet_id' => $wallet->id,
        ]);
    }

    public function releaseAppointment(Appointment $appointment, ?User $actor = null): void
    {
        DB::transaction(function () use ($appointment, $actor): void {
            $appointment = Appointment::query()->with(['payment', 'provider'])->whereKey($appointment->id)->lockForUpdate()->firstOrFail();

            if ($appointment->status !== AppointmentStatus::Completed || ! $appointment->payment || $appointment->payment->status !== PaymentStatus::Verified) {
                return;
            }

            $wallet = $this->walletService->walletForProvider($appointment->provider, $appointment->currency);
            $hold = $wallet->transactions()
                ->where('idempotency_key', 'payment:'.$appointment->payment_id.':provider_hold')
                ->where('status', WalletTransactionStatus::Posted)
                ->first();

            if (! $hold) {
                return;
            }

            $release = $this->createTransaction($wallet, [
                'source_type' => Appointment::class,
                'source_id' => $appointment->id,
                'type' => WalletTransactionType::Release,
                'gross_amount' => $hold->gross_amount,
                'commission_amount' => $hold->commission_amount,
                'net_amount' => $hold->net_amount,
                'status' => WalletTransactionStatus::Posted,
                'description' => 'Doctor appointment earning released after completion.',
                'metadata' => ['payment_id' => $appointment->payment_id],
                'created_by' => $actor?->id,
                'idempotency_key' => 'appointment:'.$appointment->id.':provider_release',
            ]);

            $this->auditLogService->log('wallet.appointment_earning_released', $release, $actor, metadata: [
                'appointment_id' => $appointment->id,
                'payment_id' => $appointment->payment_id,
                'wallet_id' => $wallet->id,
            ]);
        });
    }

    public function reversePayment(Payment $payment, ?User $actor = null, string $reason = 'Payment reversal required.'): void
    {
        DB::transaction(function () use ($payment, $actor, $reason): void {
            $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($payment->payable_type !== Appointment::class) {
                return;
            }

            $appointment = Appointment::query()->with('provider')->whereKey($payment->payable_id)->firstOrFail();
            $wallet = $this->walletService->walletForProvider($appointment->provider, $payment->currency);
            $hold = $wallet->transactions()->where('idempotency_key', 'payment:'.$payment->id.':provider_hold')->first();

            if (! $hold) {
                return;
            }

            $reversal = $this->createTransaction($wallet, [
                'source_type' => Payment::class,
                'source_id' => $payment->id,
                'type' => WalletTransactionType::Reversal,
                'gross_amount' => $hold->gross_amount,
                'commission_amount' => $hold->commission_amount,
                'net_amount' => $hold->net_amount,
                'status' => WalletTransactionStatus::Posted,
                'description' => $reason,
                'metadata' => ['appointment_id' => $appointment->id],
                'created_by' => $actor?->id,
                'idempotency_key' => 'payment:'.$payment->id.':reversal',
            ]);

            $this->auditLogService->log('wallet.payment_reversal_recorded', $reversal, $actor, metadata: [
                'payment_id' => $payment->id,
                'appointment_id' => $appointment->id,
            ]);
        });
    }

    public function releasePharmacyOrder(PharmacyOrder $order, ?User $actor = null): void
    {
        DB::transaction(function () use ($order, $actor): void {
            $order = PharmacyOrder::query()->with(['payment', 'pharmacy'])->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->order_status !== PharmacyOrderStatus::Delivered || ! $order->payment || $order->payment->status !== PaymentStatus::Verified) {
                return;
            }

            $wallet = $this->walletService->walletForProvider($order->pharmacy, $order->currency);
            $hold = $wallet->transactions()
                ->where('idempotency_key', 'payment:'.$order->payment_id.':provider_hold')
                ->where('status', WalletTransactionStatus::Posted)
                ->first();

            if (! $hold) {
                return;
            }

            $release = $this->createTransaction($wallet, [
                'source_type' => PharmacyOrder::class,
                'source_id' => $order->id,
                'type' => WalletTransactionType::Release,
                'gross_amount' => $hold->gross_amount,
                'commission_amount' => $hold->commission_amount,
                'net_amount' => $hold->net_amount,
                'status' => WalletTransactionStatus::Posted,
                'description' => 'Pharmacy order earning released after delivery.',
                'metadata' => ['payment_id' => $order->payment_id],
                'created_by' => $actor?->id,
                'idempotency_key' => 'pharmacy_order:'.$order->id.':provider_release',
            ]);

            $this->auditLogService->log('wallet.pharmacy_order_earning_released', $release, $actor, metadata: [
                'pharmacy_order_id' => $order->id,
                'payment_id' => $order->payment_id,
                'wallet_id' => $wallet->id,
            ]);
        });
    }

    public function releaseLabOrder(LabOrder $order, ?User $actor = null): void
    {
        DB::transaction(function () use ($order, $actor): void {
            $order = LabOrder::query()->with(['payment', 'lab'])->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->order_status !== LabOrderStatus::Completed || ! $order->payment || $order->payment->status !== PaymentStatus::Verified) {
                return;
            }

            $wallet = $this->walletService->walletForProvider($order->lab, $order->currency);
            $hold = $wallet->transactions()
                ->where('idempotency_key', 'payment:'.$order->payment_id.':provider_hold')
                ->where('status', WalletTransactionStatus::Posted)
                ->first();

            if (! $hold) {
                return;
            }

            $release = $this->createTransaction($wallet, [
                'source_type' => LabOrder::class,
                'source_id' => $order->id,
                'type' => WalletTransactionType::Release,
                'gross_amount' => $hold->gross_amount,
                'commission_amount' => $hold->commission_amount,
                'net_amount' => $hold->net_amount,
                'status' => WalletTransactionStatus::Posted,
                'description' => 'Lab order earning released after completion.',
                'metadata' => ['payment_id' => $order->payment_id],
                'created_by' => $actor?->id,
                'idempotency_key' => 'lab_order:'.$order->id.':provider_release',
            ]);

            $this->auditLogService->log('wallet.lab_order_earning_released', $release, $actor, metadata: [
                'lab_order_id' => $order->id,
                'payment_id' => $order->payment_id,
                'wallet_id' => $wallet->id,
            ]);
        });
    }

    private function createTransaction(Wallet $wallet, array $data): WalletTransaction
    {
        try {
            return WalletTransaction::query()->firstOrCreate(
                ['idempotency_key' => $data['idempotency_key']],
                ['wallet_id' => $wallet->id, ...$data],
            );
        } catch (QueryException $exception) {
            $transaction = WalletTransaction::query()
                ->where('idempotency_key', $data['idempotency_key'])
                ->first();

            if ($transaction) {
                return $transaction;
            }

            throw $exception;
        }
    }
}
