<?php

namespace App\Modules\AdminOperations\Infrastructure\Models;

use App\Models\User;
use App\Modules\Payments\Infrastructure\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundRequest extends Model
{
    public const STATUS_REQUESTED = 'requested';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_PROCESSED = 'processed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'refund_number',
        'user_id',
        'payment_id',
        'context_type',
        'context_id',
        'amount',
        'currency',
        'reason',
        'status',
        'admin_note',
        'resolved_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'resolved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (RefundRequest $refund): void {
            $refund->refund_number ??= static::nextNumber();
        });
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_REQUESTED, self::STATUS_UNDER_REVIEW]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_REQUESTED,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_PROCESSED,
            self::STATUS_CANCELLED,
        ];
    }

    private static function nextNumber(): string
    {
        return 'REF-'.now()->format('Ymd').'-'.str_pad((string) (static::query()->count() + 1), 5, '0', STR_PAD_LEFT);
    }
}
