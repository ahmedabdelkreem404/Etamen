<?php

namespace App\Modules\Wallets\Infrastructure\Models;

use App\Modules\Wallets\Domain\Enums\WalletTransactionStatus;
use App\Modules\Wallets\Domain\Enums\WalletTransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'source_type',
        'source_id',
        'type',
        'gross_amount',
        'commission_amount',
        'net_amount',
        'balance_after_snapshot',
        'status',
        'description',
        'metadata',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => WalletTransactionType::class,
            'gross_amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'balance_after_snapshot' => 'decimal:2',
            'status' => WalletTransactionStatus::class,
            'metadata' => 'array',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
