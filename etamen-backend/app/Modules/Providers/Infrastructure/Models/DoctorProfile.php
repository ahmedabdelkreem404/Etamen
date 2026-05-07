<?php

namespace App\Modules\Providers\Infrastructure\Models;

use App\Models\User;
use App\Modules\Appointments\Infrastructure\Models\AppointmentReview;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\Appointments\Infrastructure\Models\AppointmentSlot;
use App\Modules\Appointments\Infrastructure\Models\DoctorHoliday;
use App\Modules\Appointments\Infrastructure\Models\DoctorSchedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class DoctorProfile extends Model
{
    protected $fillable = [
        'provider_id',
        'user_id',
        'title',
        'bio_ar',
        'bio_en',
        'avatar_path',
        'consultation_fee',
        'years_of_experience',
    ];

    protected function casts(): array
    {
        return [
            'consultation_fee' => 'decimal:2',
            'years_of_experience' => 'integer',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class, 'doctor_specialties')->withTimestamps();
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar_path) {
            return null;
        }

        $path = ltrim(str_replace('\\', '/', $this->avatar_path), '/');

        if (
            Str::contains($path, ['..', '://']) ||
            Str::startsWith($path, ['storage/medical', 'medical-private', 'medical_private', 'private', 'provider-documents'])
        ) {
            return null;
        }

        return asset($path);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(AppointmentReview::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    public function holidays(): HasMany
    {
        return $this->hasMany(DoctorHoliday::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(AppointmentSlot::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
