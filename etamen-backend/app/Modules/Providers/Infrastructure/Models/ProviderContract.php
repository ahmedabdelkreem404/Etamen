<?php

namespace App\Modules\Providers\Infrastructure\Models;

use App\Modules\Providers\Domain\Enums\ProviderContractStatus;
use App\Modules\Providers\Domain\Enums\ProviderContractType;
use App\Modules\Providers\Domain\Enums\ProviderSettlementCycle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderContract extends Model
{
    protected $fillable = [
        'provider_id',
        'contract_type',
        'commission_rate',
        'fixed_commission_amount',
        'subscription_plan_id',
        'settlement_cycle',
        'pay_at_branch_allowed',
        'online_payment_required',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'contract_type' => ProviderContractType::class,
            'commission_rate' => 'decimal:2',
            'fixed_commission_amount' => 'decimal:2',
            'settlement_cycle' => ProviderSettlementCycle::class,
            'pay_at_branch_allowed' => 'boolean',
            'online_payment_required' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'status' => ProviderContractStatus::class,
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', ProviderContractStatus::Active)
            ->where(fn (Builder $dateQuery) => $dateQuery->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $dateQuery) => $dateQuery->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
