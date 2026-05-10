<?php

namespace App\Modules\AdminOperations\Infrastructure\Models;

use App\Models\User;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    public const STATUS_OPEN = 'open';
    public const STATUS_PENDING_ADMIN = 'pending_admin';
    public const STATUS_PENDING_USER = 'pending_user';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';

    public const CATEGORY_PAYMENT = 'payment';
    public const CATEGORY_BOOKING = 'booking';
    public const CATEGORY_PROVIDER = 'provider';
    public const CATEGORY_TECHNICAL = 'technical';
    public const CATEGORY_MEDICAL_SAFETY = 'medical_safety';
    public const CATEGORY_REFUND = 'refund';
    public const CATEGORY_OTHER = 'other';

    protected $fillable = [
        'ticket_number',
        'user_id',
        'provider_id',
        'category',
        'subject',
        'description',
        'status',
        'priority',
        'source',
        'assigned_admin_id',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'closed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SupportTicket $ticket): void {
            $ticket->ticket_number ??= static::nextNumber('SUP');
        });
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [self::STATUS_RESOLVED, self::STATUS_CLOSED]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportTicketMessage::class, 'ticket_id');
    }

    public static function categories(): array
    {
        return [
            self::CATEGORY_PAYMENT,
            self::CATEGORY_BOOKING,
            self::CATEGORY_PROVIDER,
            self::CATEGORY_TECHNICAL,
            self::CATEGORY_MEDICAL_SAFETY,
            self::CATEGORY_REFUND,
            self::CATEGORY_OTHER,
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_OPEN,
            self::STATUS_PENDING_ADMIN,
            self::STATUS_PENDING_USER,
            self::STATUS_RESOLVED,
            self::STATUS_CLOSED,
        ];
    }

    public static function priorities(): array
    {
        return ['low', 'normal', 'high', 'urgent'];
    }

    private static function nextNumber(string $prefix): string
    {
        return $prefix.'-'.now()->format('Ymd').'-'.str_pad((string) (static::query()->count() + 1), 5, '0', STR_PAD_LEFT);
    }
}
