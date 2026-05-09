<?php

namespace App\Modules\Radiology\Policies;

use App\Models\User;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Radiology\Infrastructure\Models\RadiologyResult;

class RadiologyResultPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }

    public function view(User $user, RadiologyResult $result): bool
    {
        return $this->canAccessResult($user, $result, requireVisibleForPatient: true);
    }

    public function download(User $user, RadiologyResult $result): bool
    {
        return $this->canAccessResult($user, $result, requireVisibleForPatient: true);
    }

    private function canAccessResult(User $user, RadiologyResult $result, bool $requireVisibleForPatient): bool
    {
        $order = $result->order;

        if ((int) $order->patient_user_id === (int) $user->id) {
            return ! $requireVisibleForPatient || $result->is_visible_to_patient;
        }

        $provider = Provider::query()->find($order->provider_id);

        return $provider
            && $provider->type === ProviderType::Radiology
            && $user->ownsProvider($provider);
    }
}
