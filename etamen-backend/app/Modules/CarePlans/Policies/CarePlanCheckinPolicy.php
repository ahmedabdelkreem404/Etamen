<?php

namespace App\Modules\CarePlans\Policies;

use App\Models\User;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanCheckin;

class CarePlanCheckinPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }

    public function view(User $user, CarePlanCheckin $checkin): bool
    {
        return (int) $checkin->patient_user_id === (int) $user->id
            || app(CarePlanPolicy::class)->view($user, $checkin->plan);
    }

    public function update(User $user, CarePlanCheckin $checkin): bool
    {
        return (int) $checkin->patient_user_id === (int) $user->id;
    }
}
