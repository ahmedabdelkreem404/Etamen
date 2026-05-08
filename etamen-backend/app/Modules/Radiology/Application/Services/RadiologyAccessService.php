<?php

namespace App\Modules\Radiology\Application\Services;

use App\Models\User;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class RadiologyAccessService
{
    public function currentRadiologyFor(User $user): Provider
    {
        $provider = $user->ownedProviders()
            ->where('type', ProviderType::Radiology)
            ->first();

        if (! $provider) {
            throw (new ModelNotFoundException)->setModel(Provider::class);
        }

        $this->assertRadiologyCanManageCatalog($provider);

        return $provider;
    }

    public function assertRadiologyCanManageCatalog(Provider $provider): void
    {
        if ($provider->type !== ProviderType::Radiology) {
            throw ValidationException::withMessages([
                'provider_id' => ['The selected provider is not a radiology provider.'],
            ]);
        }

        if ($provider->status === ProviderStatus::Suspended) {
            throw ValidationException::withMessages([
                'provider_id' => ['Suspended radiology providers cannot manage scan catalog.'],
            ]);
        }
    }
}
