<?php

namespace App\Modules\Wallets\Infrastructure\Models;

use App\Modules\Wallets\Domain\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderSubscription extends Model
{
    protected $fillable = [
        'provider_id',
        'plan_id',
        'status',
        'starts_at',
        'ends_at',
        'payment_id',
        'auto_renew',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'auto_renew' => 'boolean',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }
}
