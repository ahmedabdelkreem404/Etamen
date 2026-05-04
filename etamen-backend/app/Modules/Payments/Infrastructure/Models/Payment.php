<?php

namespace App\Modules\Payments\Infrastructure\Models;

use App\Models\User;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = [
        'payable_type',
        'payable_id',
        'user_id',
        'provider_id',
        'provider_type',
        'payment_method_id',
        'amount',
        'currency',
        'status',
        'expires_at',
        'verified_at',
        'rejected_at',
        'created_by',
        'reviewed_by',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => PaymentStatus::class,
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'rejected_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(PaymentStatusHistory::class);
    }
}
