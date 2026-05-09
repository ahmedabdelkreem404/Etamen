<?php

namespace App\Modules\Fitness\Infrastructure\Models;

use App\Modules\Fitness\Domain\Enums\CoachAvailabilityStatus;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class CoachAvailabilitySlot extends Model
{
    protected $fillable = [
        'provider_id',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'status' => CoachAvailabilityStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (CoachAvailabilitySlot $slot): void {
            $provider = Provider::query()->find($slot->provider_id);

            if (! $provider || ! in_array($provider->type, [ProviderType::FitnessCoach, ProviderType::NutritionCoach], true)) {
                throw ValidationException::withMessages([
                    'provider_id' => ['The selected provider must be a fitness or nutrition coach.'],
                ]);
            }

            if ($provider->status === ProviderStatus::Suspended) {
                throw ValidationException::withMessages([
                    'provider_id' => ['Suspended coaches cannot manage availability.'],
                ]);
            }
        });
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('coach_availability_slots.status', CoachAvailabilityStatus::Available)
            ->where('starts_at', '>=', now())
            ->whereHas('provider', fn (Builder $providerQuery) => $providerQuery
                ->publiclyVisible()
                ->whereIn('type', [ProviderType::FitnessCoach, ProviderType::NutritionCoach]));
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
