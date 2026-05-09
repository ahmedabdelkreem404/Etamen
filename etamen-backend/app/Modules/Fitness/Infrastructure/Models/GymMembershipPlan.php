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

class GymMembershipPlan extends Model
{
    protected $fillable = [
        'provider_id',
        'branch_id',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'duration_days',
        'price',
        'sessions_count',
        'includes_classes',
        'includes_personal_training',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'duration_days' => 'integer',
            'price' => 'decimal:2',
            'sessions_count' => 'integer',
            'includes_classes' => 'boolean',
            'includes_personal_training' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (GymMembershipPlan $plan): void {
            static::assertGymProvider($plan->provider_id);
            static::assertBranchBelongsToProvider($plan->branch_id, $plan->provider_id);
        });
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('gym_membership_plans.is_active', true)
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

    private static function assertGymProvider(int|string|null $providerId): void
    {
        $provider = Provider::query()->find($providerId);

        if (! $provider || $provider->type !== ProviderType::Gym) {
            throw ValidationException::withMessages([
                'provider_id' => ['The selected provider must be a gym provider.'],
            ]);
        }

        if ($provider->status === ProviderStatus::Suspended) {
            throw ValidationException::withMessages([
                'provider_id' => ['Suspended gym providers cannot manage plans.'],
            ]);
        }
    }

    private static function assertBranchBelongsToProvider(int|string|null $branchId, int|string|null $providerId): void
    {
        if (! $branchId) {
            return;
        }

        $branchProviderId = ProviderBranch::query()->whereKey($branchId)->value('provider_id');

        if ((int) $branchProviderId !== (int) $providerId) {
            throw ValidationException::withMessages([
                'branch_id' => ['The selected branch does not belong to this gym provider.'],
            ]);
        }
    }
}
