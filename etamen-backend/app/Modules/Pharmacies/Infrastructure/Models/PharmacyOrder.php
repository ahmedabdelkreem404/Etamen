<?php

namespace App\Modules\Pharmacies\Infrastructure\Models;

use App\Models\User;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Pharmacies\Domain\Enums\PharmacyDeliveryMethod;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderPaymentStatus;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderStatus;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PharmacyOrder extends Model
{
    protected $fillable = [
        'order_number',
        'patient_user_id',
        'pharmacy_provider_id',
        'prescription_id',
        'payment_id',
        'subtotal',
        'discount_total',
        'commission_amount',
        'provider_net_amount',
        'grand_total',
        'currency',
        'payment_status',
        'order_status',
        'delivery_method',
        'delivery_address',
        'notes',
        'paid_at',
        'accepted_at',
        'rejected_at',
        'delivered_at',
        'cancelled_at',
        'stock_reserved_at',
        'stock_released_at',
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
            'payment_status' => PharmacyOrderPaymentStatus::class,
            'order_status' => PharmacyOrderStatus::class,
            'delivery_method' => PharmacyDeliveryMethod::class,
            'paid_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'stock_reserved_at' => 'datetime',
            'stock_released_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'pharmacy_provider_id');
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(PharmacyPrescription::class, 'prescription_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PharmacyOrderItem::class, 'order_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(PharmacyOrderStatusHistory::class, 'order_id');
    }
}
