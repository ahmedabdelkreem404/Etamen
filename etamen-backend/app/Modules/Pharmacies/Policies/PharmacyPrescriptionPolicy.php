<?php

namespace App\Modules\Pharmacies\Policies;

use App\Models\User;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyPrescription;
use App\Modules\Providers\Infrastructure\Models\Provider;

class PharmacyPrescriptionPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }

    public function view(User $user, PharmacyPrescription $prescription): bool
    {
        if ((int) $prescription->patient_user_id === (int) $user->id) {
            return true;
        }

        $provider = Provider::query()->find($prescription->pharmacy_provider_id);

        return $provider && $user->ownsProvider($provider);
    }
}
