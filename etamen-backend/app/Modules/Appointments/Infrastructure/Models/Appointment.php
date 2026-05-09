<?php

namespace App\Modules\Appointments\Infrastructure\Models;

use App\Models\User;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Domain\Enums\ConsultationType;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Providers\Infrastructure\Models\DoctorProfile;
use App\Modules\Providers\Infrastructure\Models\HospitalDepartment;
use App\Modules\Providers\Infrastructure\Models\HospitalDoctor;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    protected $fillable = [
        'appointment_number',
        'patient_user_id',
        'doctor_profile_id',
        'provider_id',
        'hospital_provider_id',
        'hospital_department_id',
        'hospital_doctor_id',
        'branch_id',
        'appointment_slot_id',
        'consultation_type',
        'problem_description',
        'price',
        'currency',
        'status',
        'payment_id',
        'booked_at',
        'confirmed_at',
        'accepted_at',
        'rejected_at',
        'cancelled_at',
        'completed_at',
        'no_show_at',
    ];

    protected function casts(): array
    {
        return [
            'consultation_type' => ConsultationType::class,
            'price' => 'decimal:2',
            'status' => AppointmentStatus::class,
            'booked_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
            'no_show_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }

    public function doctorProfile(): BelongsTo
    {
        return $this->belongsTo(DoctorProfile::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'hospital_provider_id');
    }

    public function hospitalDepartment(): BelongsTo
    {
        return $this->belongsTo(HospitalDepartment::class);
    }

    public function hospitalDoctorLink(): BelongsTo
    {
        return $this->belongsTo(HospitalDoctor::class, 'hospital_doctor_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(ProviderBranch::class);
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(AppointmentSlot::class, 'appointment_slot_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(AppointmentStatusHistory::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(AppointmentNote::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(AppointmentReview::class);
    }
}
