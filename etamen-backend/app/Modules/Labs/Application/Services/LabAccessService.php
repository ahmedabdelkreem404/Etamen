<?php

namespace App\Modules\Labs\Application\Services;

use App\Models\User;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class LabAccessService
{
    public function currentLabFor(User $user): Provider
    {
        $provider = $user->ownedProviders()
            ->where('type', ProviderType::Lab)
            ->first();

        if (! $provider) {
            throw (new ModelNotFoundException)->setModel(Provider::class);
        }

        return $provider;
    }

    public function publicLab(int $providerId): Provider
    {
        return Provider::query()
            ->publiclyVisible()
            ->where('type', ProviderType::Lab)
            ->findOrFail($providerId);
    }

    public function assertApprovedLab(Provider $provider): void
    {
        if ($provider->type !== ProviderType::Lab || $provider->status !== ProviderStatus::Approved || ! $provider->is_active) {
            throw ValidationException::withMessages([
                'lab_provider_id' => ['The selected lab is not available.'],
            ]);
        }
    }
}
