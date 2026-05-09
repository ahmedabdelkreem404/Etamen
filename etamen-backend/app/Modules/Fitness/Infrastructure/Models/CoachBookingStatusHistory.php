<?php

namespace App\Modules\Fitness\Infrastructure\Models;

use App\Models\User;
use App\Modules\Fitness\Domain\Enums\CoachBookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachBookingStatusHistory extends Model
{
    protected $fillable = [
        'coach_booking_id',
        'from_status',
        'to_status',
        'changed_by',
        'reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'to_status' => CoachBookingStatus::class,
            'metadata' => 'array',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(CoachBooking::class, 'coach_booking_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
