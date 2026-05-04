<?php

namespace App\Modules\Appointments\Infrastructure\Models;

use App\Modules\Providers\Infrastructure\Models\DoctorProfile;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DoctorSchedule extends Model
{
    protected $fillable = [
        'doctor_profile_id',
        'provider_id',
        'branch_id',
        'name',
        'is_active',
        'slot_duration_minutes',
        'buffer_minutes',
        'max_days_ahead',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'slot_duration_minutes' => 'integer',
            'buffer_minutes' => 'integer',
            'max_days_ahead' => 'integer',
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

    public function days(): HasMany
    {
        return $this->hasMany(DoctorScheduleDay::class);
    }
}
