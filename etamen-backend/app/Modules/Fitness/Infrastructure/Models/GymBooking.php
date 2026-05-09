<?php

namespace App\Modules\Fitness\Infrastructure\Models;

use App\Models\User;
use App\Modules\Fitness\Domain\Enums\GymBookingStatus;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GymBooking extends Model
{
    protected $fillable = [
        'booking_number',
        'patient_user_id',
        'provider_id',
        'branch_id',
        'membership_plan_id',
        'gym_class_id',
        'status',
        'total_amount',
        'payment_id',
        'starts_at',
        'ends_at',
        'notes',
        'cancelled_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => GymBookingStatus::class,
            'total_amount' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(ProviderBranch::class);
    }

    public function membershipPlan(): BelongsTo
    {
        return $this->belongsTo(GymMembershipPlan::class, 'membership_plan_id');
    }

    public function gymClass(): BelongsTo
    {
        return $this->belongsTo(GymClassModel::class, 'gym_class_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(GymBookingStatusHistory::class);
    }
}
