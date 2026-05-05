<?php

namespace App\Modules\CarePlans\Policies;

use App\Models\User;
use App\Modules\CarePlans\Domain\Enums\CarePlanSource;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;

class CarePlanPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }

    public function view(User $user, CarePlan $plan): bool
    {
        return (int) $plan->patient_user_id === (int) $user->id
            || $this->ownsAssignedProvider($user, $plan);
    }

    public function update(User $user, CarePlan $plan): bool
    {
        return ((int) $plan->patient_user_id === (int) $user->id && $plan->source === CarePlanSource::PatientCreated)
            || $this->ownsAssignedProvider($user, $plan);
    }

    public function delete(User $user, CarePlan $plan): bool
    {
        return (int) $plan->patient_user_id === (int) $user->id && $plan->source === CarePlanSource::PatientCreated;
    }

    public function manageStructure(User $user, CarePlan $plan): bool
    {
        return ((int) $plan->patient_user_id === (int) $user->id && $plan->source === CarePlanSource::PatientCreated)
            || $this->ownsAssignedProvider($user, $plan);
    }

    public function track(User $user, CarePlan $plan): bool
    {
        return (int) $plan->patient_user_id === (int) $user->id;
    }

    private function ownsAssignedProvider(User $user, CarePlan $plan): bool
    {
        return $plan->provider_id
            && $user->ownedProviders()->whereKey($plan->provider_id)->exists();
    }
}
