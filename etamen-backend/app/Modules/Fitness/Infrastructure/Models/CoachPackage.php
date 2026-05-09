<?php

namespace App\Modules\Fitness\Infrastructure\Models;

use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class CoachPackage extends Model
{
    protected $fillable = [
        'provider_id',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'sessions_count',
        'duration_days',
        'price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sessions_count' => 'integer',
            'duration_days' => 'integer',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (CoachPackage $package): void {
            $provider = Provider::query()->find($package->provider_id);

            if (! $provider || ! in_array($provider->type, [ProviderType::FitnessCoach, ProviderType::NutritionCoach], true)) {
                throw ValidationException::withMessages([
                    'provider_id' => ['The selected provider must be a fitness or nutrition coach.'],
                ]);
            }
        });
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('coach_packages.is_active', true)
            ->whereHas('provider', fn (Builder $providerQuery) => $providerQuery
                ->publiclyVisible()
                ->whereIn('type', [ProviderType::FitnessCoach, ProviderType::NutritionCoach]));
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
