<?php

namespace App\Modules\Radiology\Infrastructure\Models;

use App\Models\User;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderBranch;
use App\Modules\Radiology\Domain\Enums\RadiologyOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RadiologyOrder extends Model
{
    protected $fillable = [
        'order_number',
        'patient_user_id',
        'provider_id',
        'branch_id',
        'status',
        'subtotal',
        'discount_amount',
        'total_amount',
        'payment_id',
        'scheduled_at',
        'patient_notes',
        'provider_notes',
        'accepted_at',
        'rejected_at',
        'completed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => RadiologyOrderStatus::class,
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'scheduled_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(ProviderBranch::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RadiologyOrderItem::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(RadiologyResult::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(RadiologyOrderStatusHistory::class);
    }
}
