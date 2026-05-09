<?php

namespace App\Modules\Fitness\Infrastructure\Models;

use App\Models\User;
use App\Modules\Fitness\Domain\Enums\GymBookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GymBookingStatusHistory extends Model
{
    protected $fillable = [
        'gym_booking_id',
        'from_status',
        'to_status',
        'changed_by',
        'reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'to_status' => GymBookingStatus::class,
            'metadata' => 'array',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(GymBooking::class, 'gym_booking_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
