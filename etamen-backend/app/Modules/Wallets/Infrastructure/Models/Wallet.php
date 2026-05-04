<?php

namespace App\Modules\Wallets\Infrastructure\Models;

use App\Modules\Wallets\Domain\Enums\WalletOwnerType;
use App\Modules\Wallets\Domain\Enums\WalletStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    protected $fillable = [
        'owner_type',
        'owner_id',
        'currency',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'owner_type' => WalletOwnerType::class,
            'status' => WalletStatus::class,
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
