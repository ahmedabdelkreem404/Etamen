<?php

namespace App\Modules\Pharmacies\Application\Services;

use App\Models\User;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class PharmacyAccessService
{
    public function currentPharmacyFor(User $user): Provider
    {
        $provider = $user->ownedProviders()
            ->where('type', ProviderType::Pharmacy)
            ->first();

        if (! $provider) {
            throw (new ModelNotFoundException)->setModel(Provider::class);
        }

        return $provider;
    }

    public function publicPharmacy(int $providerId): Provider
    {
        $provider = Provider::query()
            ->publiclyVisible()
            ->where('type', ProviderType::Pharmacy)
            ->findOrFail($providerId);

        return $provider;
    }

    public function assertApprovedPharmacy(Provider $provider): void
    {
        if ($provider->type !== ProviderType::Pharmacy || $provider->status !== ProviderStatus::Approved || ! $provider->is_active) {
            throw ValidationException::withMessages([
                'pharmacy_provider_id' => ['The selected pharmacy is not available.'],
            ]);
        }
    }
}
