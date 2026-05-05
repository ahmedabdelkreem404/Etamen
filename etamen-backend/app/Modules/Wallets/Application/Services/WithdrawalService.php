<?php

namespace App\Modules\Wallets\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Wallets\Domain\Enums\WalletStatus;
use App\Modules\Wallets\Domain\Enums\WalletTransactionStatus;
use App\Modules\Wallets\Domain\Enums\WalletTransactionType;
use App\Modules\Wallets\Domain\Enums\WithdrawalRequestStatus;
use App\Modules\Wallets\Infrastructure\Models\Wallet;
use App\Modules\Wallets\Infrastructure\Models\WalletTransaction;
use App\Modules\Wallets\Infrastructure\Models\WithdrawalRequest;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WithdrawalService
{
    public function __construct(
        private readonly WalletBalanceService $balanceService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function request(User $user, Wallet $wallet, float $amount): WithdrawalRequest
    {
        return DB::transaction(function () use ($user, $wallet, $amount): WithdrawalRequest {
            $wallet = Wallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();
            $this->assertWithdrawable($wallet, $amount);

            $request = WithdrawalRequest::query()->create([
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'status' => WithdrawalRequestStatus::Pending,
                'requested_by' => $user->id,
            ]);

            $this->auditLogService->log('withdrawal.requested', $request, $user, metadata: ['wallet_id' => $wallet->id]);

            return $request;
        });
    }

    public function approve(User $admin, WithdrawalRequest $withdrawal): WithdrawalRequest
    {
        return DB::transaction(function () use ($admin, $withdrawal): WithdrawalRequest {
            $withdrawal = WithdrawalRequest::query()->whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();
            $this->assertStatus($withdrawal, WithdrawalRequestStatus::Pending);
            $withdrawal->update(['status' => WithdrawalRequestStatus::Approved, 'reviewed_by' => $admin->id]);
            $this->auditLogService->log('withdrawal.approved', $withdrawal, $admin);

            return $withdrawal->refresh();
        });
    }

    public function reject(User $admin, WithdrawalRequest $withdrawal, string $reason): WithdrawalRequest
    {
        return DB::transaction(function () use ($admin, $withdrawal, $reason): WithdrawalRequest {
            $withdrawal = WithdrawalRequest::query()->whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();
            $this->assertStatus($withdrawal, WithdrawalRequestStatus::Pending, WithdrawalRequestStatus::Approved);
            $withdrawal->update([
                'status' => WithdrawalRequestStatus::Rejected,
                'reviewed_by' => $admin->id,
                'rejection_reason' => $reason,
            ]);
            $this->auditLogService->log('withdrawal.rejected', $withdrawal, $admin, metadata: ['reason' => $reason]);

            return $withdrawal->refresh();
        });
    }

    public function markPaid(User $admin, WithdrawalRequest $withdrawal): WithdrawalRequest
    {
        return DB::transaction(function () use ($admin, $withdrawal): WithdrawalRequest {
            $withdrawal = WithdrawalRequest::query()->with('wallet')->whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();

            if ($withdrawal->status === WithdrawalRequestStatus::Paid) {
                return $withdrawal;
            }

            $this->assertStatus($withdrawal, WithdrawalRequestStatus::Approved);
            $this->assertWithdrawable($withdrawal->wallet, (float) $withdrawal->amount);

            $this->createWithdrawalTransaction($withdrawal, $admin);

            $withdrawal->update(['status' => WithdrawalRequestStatus::Paid, 'reviewed_by' => $admin->id, 'paid_at' => now()]);
            $this->auditLogService->log('withdrawal.paid', $withdrawal, $admin);

            return $withdrawal->refresh();
        });
    }

    private function assertWithdrawable(Wallet $wallet, float $amount): void
    {
        if ($wallet->status !== WalletStatus::Active) {
            throw ValidationException::withMessages(['wallet' => ['Wallet is not active.']]);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => ['Withdrawal amount must be greater than zero.']]);
        }

        if ($amount > $this->balanceService->summary($wallet)['available_balance']) {
            throw ValidationException::withMessages(['amount' => ['Withdrawal amount exceeds available balance.']]);
        }
    }

    private function createWithdrawalTransaction(WithdrawalRequest $withdrawal, User $admin): WalletTransaction
    {
        $idempotencyKey = 'withdrawal:'.$withdrawal->id.':paid';

        try {
            return $withdrawal->wallet->transactions()->firstOrCreate(
                ['idempotency_key' => $idempotencyKey],
                [
                    'source_type' => WithdrawalRequest::class,
                    'source_id' => $withdrawal->id,
                    'type' => WalletTransactionType::Withdrawal,
                    'gross_amount' => $withdrawal->amount,
                    'commission_amount' => 0,
                    'net_amount' => $withdrawal->amount,
                    'status' => WalletTransactionStatus::Posted,
                    'description' => 'Provider withdrawal paid manually by admin.',
                    'created_by' => $admin->id,
                ],
            );
        } catch (QueryException $exception) {
            $transaction = WalletTransaction::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($transaction) {
                return $transaction;
            }

            throw $exception;
        }
    }

    private function assertStatus(WithdrawalRequest $withdrawal, WithdrawalRequestStatus ...$statuses): void
    {
        if (! in_array($withdrawal->status, $statuses, true)) {
            throw ValidationException::withMessages(['withdrawal' => ['Withdrawal request is not in an allowed status.']]);
        }
    }
}
