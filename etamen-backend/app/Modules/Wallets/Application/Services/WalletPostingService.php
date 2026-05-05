<?php

namespace App\Modules\Wallets\Application\Services;

use App\Models\User;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Infrastructure\Models\Payment;
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

            if ($payment->status !== PaymentStatus::Verified || $payment->payable_type !== Appointment::class) {
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
