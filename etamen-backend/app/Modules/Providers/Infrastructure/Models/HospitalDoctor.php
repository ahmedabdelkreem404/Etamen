<?php

namespace App\Modules\Providers\Infrastructure\Models;

use App\Modules\Providers\Domain\Enums\ProviderType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class HospitalDoctor extends Model
{
    protected $fillable = [
        'hospital_provider_id',
        'doctor_provider_id',
        'hospital_department_id',
        'consultation_fee',
        'online_consultation_enabled',
        'clinic_consultation_enabled',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'consultation_fee' => 'decimal:2',
            'online_consultation_enabled' => 'boolean',
            'clinic_consultation_enabled' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (HospitalDoctor $link): void {
            $hospital = Provider::query()->find($link->hospital_provider_id);
            $doctor = Provider::query()->find($link->doctor_provider_id);

            if (! $hospital || $hospital->type !== ProviderType::Hospital) {
                throw ValidationException::withMessages([
                    'hospital_provider_id' => ['The selected hospital provider must be type hospital.'],
                ]);
            }

            if (! $doctor || $doctor->type !== ProviderType::Doctor) {
                throw ValidationException::withMessages([
                    'doctor_provider_id' => ['The selected doctor provider must be type doctor.'],
                ]);
            }
        });
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('hospital_doctors.is_active', true)
            ->whereHas('hospital', fn (Builder $providerQuery) => $providerQuery->publiclyVisible()->where('type', ProviderType::Hospital))
            ->whereHas('doctorProvider', fn (Builder $providerQuery) => $providerQuery->publiclyVisible()->where('type', ProviderType::Doctor));
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'hospital_provider_id');
    }

    public function doctorProvider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'doctor_provider_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(HospitalDepartment::class, 'hospital_department_id');
    }
}
