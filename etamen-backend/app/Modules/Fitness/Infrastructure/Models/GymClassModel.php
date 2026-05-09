<?php

namespace App\Modules\Fitness\Infrastructure\Models;

use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderBranch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class GymClassModel extends Model
{
    protected $table = 'gym_classes';

    protected $fillable = [
        'provider_id',
        'branch_id',
        'coach_provider_id',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'starts_at',
        'ends_at',
        'capacity',
        'price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'capacity' => 'integer',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (GymClassModel $class): void {
            $provider = Provider::query()->find($class->provider_id);

            if (! $provider || $provider->type !== ProviderType::Gym) {
                throw ValidationException::withMessages([
                    'provider_id' => ['The selected provider must be a gym provider.'],
                ]);
            }

            if ($provider->status === ProviderStatus::Suspended) {
                throw ValidationException::withMessages([
                    'provider_id' => ['Suspended gym providers cannot manage classes.'],
                ]);
            }

            if ($class->branch_id) {
                $branchProviderId = ProviderBranch::query()->whereKey($class->branch_id)->value('provider_id');

                if ((int) $branchProviderId !== (int) $class->provider_id) {
                    throw ValidationException::withMessages([
                        'branch_id' => ['The selected branch does not belong to this gym provider.'],
                    ]);
                }
            }

            if ($class->coach_provider_id) {
                $coach = Provider::query()->find($class->coach_provider_id);

                if (! $coach || ! in_array($coach->type, [ProviderType::FitnessCoach, ProviderType::NutritionCoach], true)) {
                    throw ValidationException::withMessages([
                        'coach_provider_id' => ['The selected coach must be a fitness or nutrition coach.'],
                    ]);
                }
            }
        });
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('gym_classes.is_active', true)
            ->where('starts_at', '>=', now()->subDay())
            ->whereHas('provider', fn (Builder $providerQuery) => $providerQuery
                ->publiclyVisible()
                ->where('type', ProviderType::Gym));
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(ProviderBranch::class);
    }

    public function coachProvider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'coach_provider_id');
    }
}
