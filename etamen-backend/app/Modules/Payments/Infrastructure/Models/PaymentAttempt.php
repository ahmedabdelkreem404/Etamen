<?php

namespace App\Modules\Payments\Infrastructure\Models;

use App\Modules\Payments\Domain\Enums\PaymentMethodType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAttempt extends Model
{
    protected $fillable = [
        'payment_id',
        'method_type',
        'gateway_reference',
        'request_payload',
        'response_payload',
        'status',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'method_type' => PaymentMethodType::class,
            'request_payload' => 'array',
            'response_payload' => 'array',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
