<?php

namespace App\Modules\Fitness\Infrastructure\Models;

use App\Models\User;
use App\Modules\Fitness\Domain\Enums\CoachBookingStatus;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoachBooking extends Model
{
    protected $fillable = [
        'booking_number',
        'patient_user_id',
        'coach_provider_id',
        'session_type_id',
        'availability_slot_id',
        'status',
        'total_amount',
        'payment_id',
        'patient_goal',
        'coach_notes',
        'cancelled_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => CoachBookingStatus::class,
            'total_amount' => 'decimal:2',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }

    public function coachProvider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'coach_provider_id');
    }

    public function sessionType(): BelongsTo
    {
        return $this->belongsTo(CoachSessionType::class, 'session_type_id');
    }

    public function availabilitySlot(): BelongsTo
    {
        return $this->belongsTo(CoachAvailabilitySlot::class, 'availability_slot_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(CoachBookingStatusHistory::class);
    }
}
