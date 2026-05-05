<?php

namespace App\Modules\Labs\Infrastructure\Models;

use App\Models\User;
use App\Modules\Labs\Domain\Enums\LabOrderPaymentStatus;
use App\Modules\Labs\Domain\Enums\LabOrderStatus;
use App\Modules\Labs\Domain\Enums\LabSampleCollectionMethod;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabOrder extends Model
{
    protected $fillable = [
        'order_number',
        'patient_user_id',
        'lab_provider_id',
        'payment_id',
        'subtotal',
        'discount_total',
        'commission_amount',
        'provider_net_amount',
        'grand_total',
        'currency',
        'payment_status',
        'order_status',
        'sample_collection_method',
        'collection_address',
        'scheduled_at',
        'paid_at',
        'accepted_at',
        'rejected_at',
        'sample_collected_at',
        'result_ready_at',
        'completed_at',
        'cancelled_at',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'provider_net_amount' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'payment_status' => LabOrderPaymentStatus::class,
            'order_status' => LabOrderStatus::class,
            'sample_collection_method' => LabSampleCollectionMethod::class,
            'scheduled_at' => 'datetime',
            'paid_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
            'sample_collected_at' => 'datetime',
            'result_ready_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'lab_provider_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(LabOrderItem::class, 'order_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(LabResult::class, 'order_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(LabOrderStatusHistory::class, 'order_id');
    }
}
