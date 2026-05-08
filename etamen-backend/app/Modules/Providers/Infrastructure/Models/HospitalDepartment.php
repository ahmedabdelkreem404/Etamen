<?php

namespace App\Modules\Providers\Infrastructure\Models;

use App\Modules\Providers\Domain\Enums\ProviderType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class HospitalDepartment extends Model
{
    protected $fillable = [
        'hospital_provider_id',
        'specialty_id',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (HospitalDepartment $department): void {
            $hospital = Provider::query()->find($department->hospital_provider_id);

            if (! $hospital || $hospital->type !== ProviderType::Hospital) {
                throw ValidationException::withMessages([
                    'hospital_provider_id' => ['The selected provider must be a hospital.'],
                ]);
            }
        });
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('hospital_departments.is_active', true)
            ->whereHas('hospital', fn (Builder $providerQuery) => $providerQuery->publiclyVisible()->where('type', ProviderType::Hospital));
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'hospital_provider_id');
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    public function doctors(): HasMany
    {
        return $this->hasMany(HospitalDoctor::class);
    }
}
