<?php

namespace App\Modules\Wallets\Infrastructure\Models;

use App\Models\User;
use App\Modules\Wallets\Domain\Enums\WithdrawalRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalRequest extends Model
{
    protected $fillable = [
        'wallet_id',
        'amount',
        'status',
        'requested_by',
        'reviewed_by',
        'rejection_reason',
        'paid_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => WithdrawalRequestStatus::class,
            'paid_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
