<?php

namespace App\Modules\AdminOperations\Infrastructure\Models;

use App\Models\User;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dispute extends Model
{
    public const STATUS_OPEN = 'open';
    public const STATUS_INVESTIGATING = 'investigating';
    public const STATUS_WAITING_USER = 'waiting_user';
    public const STATUS_WAITING_PROVIDER = 'waiting_provider';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'dispute_number',
        'user_id',
        'provider_id',
        'payment_id',
        'context_type',
        'context_id',
        'reason',
        'status',
        'priority',
        'assigned_admin_id',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Dispute $dispute): void {
            $dispute->dispute_number ??= static::nextNumber();
        });
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [
            self::STATUS_OPEN,
            self::STATUS_INVESTIGATING,
            self::STATUS_WAITING_USER,
            self::STATUS_WAITING_PROVIDER,
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_OPEN,
            self::STATUS_INVESTIGATING,
            self::STATUS_WAITING_USER,
            self::STATUS_WAITING_PROVIDER,
            self::STATUS_RESOLVED,
            self::STATUS_REJECTED,
            self::STATUS_CLOSED,
        ];
    }

    public static function priorities(): array
    {
        return ['low', 'normal', 'high', 'urgent'];
    }

    private static function nextNumber(): string
    {
        return 'DSP-'.now()->format('Ymd').'-'.str_pad((string) (static::query()->count() + 1), 5, '0', STR_PAD_LEFT);
    }
}
