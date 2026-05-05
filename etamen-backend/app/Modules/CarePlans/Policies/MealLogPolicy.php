<?php

namespace App\Modules\CarePlans\Policies;

use App\Models\User;
use App\Modules\CarePlans\Infrastructure\Models\MealLog;

class MealLogPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }

    public function view(User $user, MealLog $log): bool
    {
        return (int) $log->patient_user_id === (int) $user->id
            || app(CarePlanPolicy::class)->view($user, $log->plan);
    }

    public function update(User $user, MealLog $log): bool
    {
        return (int) $log->patient_user_id === (int) $user->id;
    }

    public function delete(User $user, MealLog $log): bool
    {
        return (int) $log->patient_user_id === (int) $user->id;
    }
}
