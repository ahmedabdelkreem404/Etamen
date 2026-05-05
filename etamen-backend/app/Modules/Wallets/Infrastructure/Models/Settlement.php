<?php

namespace App\Modules\Wallets\Infrastructure\Models;

use App\Models\User;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Wallets\Domain\Enums\SettlementStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Settlement extends Model
{
    protected $fillable = [
        'provider_id',
        'provider_type',
        'total_gross',
        'total_commission',
        'total_net',
        'status',
        'settled_by',
        'settled_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'provider_type' => ProviderType::class,
            'total_gross' => 'decimal:2',
            'total_commission' => 'decimal:2',
            'total_net' => 'decimal:2',
            'status' => SettlementStatus::class,
            'settled_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(SettlementItem::class);
    }

    public function settledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'settled_by');
    }
}
