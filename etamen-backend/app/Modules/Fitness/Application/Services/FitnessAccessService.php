<?php

namespace App\Modules\Fitness\Application\Services;

use App\Models\User;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class FitnessAccessService
{
    public function currentGymFor(User $user): Provider
    {
        $provider = $user->ownedProviders()
            ->where('type', ProviderType::Gym)
            ->first();

        if (! $provider) {
            throw (new ModelNotFoundException)->setModel(Provider::class);
        }

        $this->assertGymCanManage($provider);

        return $provider;
    }

    public function currentCoachFor(User $user): Provider
    {
        $provider = $user->ownedProviders()
            ->whereIn('type', [ProviderType::FitnessCoach, ProviderType::NutritionCoach])
            ->first();

        if (! $provider) {
            throw (new ModelNotFoundException)->setModel(Provider::class);
        }

        $this->assertCoachCanManage($provider);

        return $provider;
    }

    public function assertGymCanManage(Provider $provider): void
    {
        if ($provider->type !== ProviderType::Gym) {
            throw ValidationException::withMessages([
                'provider_id' => ['The selected provider is not a gym provider.'],
            ]);
        }

        if ($provider->status === ProviderStatus::Suspended) {
            throw ValidationException::withMessages([
                'provider_id' => ['Suspended gym providers cannot manage fitness data.'],
            ]);
        }
    }

    public function assertCoachCanManage(Provider $provider): void
    {
        if (! in_array($provider->type, [ProviderType::FitnessCoach, ProviderType::NutritionCoach], true)) {
            throw ValidationException::withMessages([
                'provider_id' => ['The selected provider is not a fitness or nutrition coach.'],
            ]);
        }

        if ($provider->status === ProviderStatus::Suspended) {
            throw ValidationException::withMessages([
                'provider_id' => ['Suspended coaches cannot manage coaching data.'],
            ]);
        }
    }
}
