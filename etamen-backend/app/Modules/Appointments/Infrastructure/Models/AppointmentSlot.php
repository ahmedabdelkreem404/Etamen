<?php

namespace App\Modules\Appointments\Infrastructure\Models;

use App\Modules\Appointments\Domain\Enums\AppointmentSlotStatus;
use App\Modules\Providers\Infrastructure\Models\DoctorProfile;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AppointmentSlot extends Model
{
    protected $fillable = [
        'doctor_profile_id',
        'provider_id',
        'branch_id',
        'starts_at',
        'ends_at',
        'status',
        'hold_expires_at',
        'generated_from_schedule_id',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'status' => AppointmentSlotStatus::class,
            'hold_expires_at' => 'datetime',
        ];
    }

    public function doctorProfile(): BelongsTo
    {
        return $this->belongsTo(DoctorProfile::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(ProviderBranch::class);
    }

    public function appointment(): HasOne
    {
        return $this->hasOne(Appointment::class, 'appointment_slot_id');
    }
}
