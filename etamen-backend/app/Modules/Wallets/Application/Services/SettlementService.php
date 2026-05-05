<?php

namespace App\Modules\Wallets\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Wallets\Domain\Enums\SettlementStatus;
use App\Modules\Wallets\Domain\Enums\WalletOwnerType;
use App\Modules\Wallets\Domain\Enums\WalletTransactionStatus;
use App\Modules\Wallets\Domain\Enums\WalletTransactionType;
use App\Modules\Wallets\Infrastructure\Models\Settlement;
use App\Modules\Wallets\Infrastructure\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SettlementService
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function create(User $admin, int $providerId, ProviderType $providerType): Settlement
    {
        return DB::transaction(function () use ($admin, $providerId, $providerType): Settlement {
            if ($providerType !== ProviderType::Doctor) {
                throw ValidationException::withMessages(['provider_type' => ['Only doctor settlements are active in this sprint.']]);
            }

            Provider::query()
                ->whereKey($providerId)
                ->where('type', ProviderType::Doctor)
                ->firstOrFail();

            $wallet = Wallet::query()
                ->where('owner_type', WalletOwnerType::Doctor)
                ->where('owner_id', $providerId)
                ->where('currency', 'EGP')
                ->lockForUpdate()
                ->firstOrFail();

            $transactions = $wallet->transactions()
                ->where('type', WalletTransactionType::Release)
                ->where('status', WalletTransactionStatus::Posted)
                ->whereDoesntHave('settlementItem')
                ->get();

            if ($transactions->isEmpty()) {
                throw ValidationException::withMessages(['settlement' => ['No unsettled released transactions found.']]);
            }

            $settlement = Settlement::query()->create([
                'provider_id' => $providerId,
                'provider_type' => $providerType,
                'total_gross' => $transactions->sum('gross_amount'),
                'total_commission' => $transactions->sum('commission_amount'),
                'total_net' => $transactions->sum('net_amount'),
                'status' => SettlementStatus::Draft,
            ]);

            foreach ($transactions as $transaction) {
                $settlement->items()->create([
                    'wallet_transaction_id' => $transaction->id,
                    'amount' => $transaction->net_amount,
                ]);
            }

            $this->auditLogService->log('settlement.created', $settlement, $admin, metadata: ['items' => $transactions->count()]);

            return $settlement->load('items');
        });
    }

    public function markPaid(User $admin, Settlement $settlement): Settlement
    {
        return DB::transaction(function () use ($admin, $settlement): Settlement {
            $settlement = Settlement::query()->whereKey($settlement->id)->lockForUpdate()->firstOrFail();

            if ($settlement->status === SettlementStatus::Paid) {
                return $settlement->refresh();
            }

            if (! in_array($settlement->status, [SettlementStatus::Draft, SettlementStatus::Approved], true)) {
                throw ValidationException::withMessages(['settlement' => ['Settlement cannot be marked paid.']]);
            }

            $settlement->update(['status' => SettlementStatus::Paid, 'settled_by' => $admin->id, 'settled_at' => now()]);
            $this->auditLogService->log('settlement.paid', $settlement, $admin);

            return $settlement->refresh()->load('items');
        });
    }
}
