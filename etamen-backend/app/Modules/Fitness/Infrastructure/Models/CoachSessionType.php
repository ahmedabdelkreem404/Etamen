<?php

namespace App\Modules\Fitness\Infrastructure\Models;

use App\Modules\Fitness\Domain\Enums\CoachSessionMode;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class CoachSessionType extends Model
{
    protected $fillable = [
        'provider_id',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'duration_minutes',
        'price',
        'session_mode',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'price' => 'decimal:2',
            'session_mode' => CoachSessionMode::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (CoachSessionType $sessionType): void {
            static::assertCoachProvider($sessionType->provider_id);
        });
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('coach_session_types.is_active', true)
            ->whereHas('provider', fn (Builder $providerQuery) => $providerQuery
                ->publiclyVisible()
                ->whereIn('type', [ProviderType::FitnessCoach, ProviderType::NutritionCoach]));
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public static function assertCoachProvider(int|string|null $providerId): void
    {
        $provider = Provider::query()->find($providerId);

        if (! $provider || ! in_array($provider->type, [ProviderType::FitnessCoach, ProviderType::NutritionCoach], true)) {
            throw ValidationException::withMessages([
                'provider_id' => ['The selected provider must be a fitness or nutrition coach.'],
            ]);
        }

        if ($provider->status === ProviderStatus::Suspended) {
            throw ValidationException::withMessages([
                'provider_id' => ['Suspended coaches cannot manage sessions.'],
            ]);
        }
    }
}
