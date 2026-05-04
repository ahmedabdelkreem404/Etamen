<?php

namespace App\Modules\Appointments\Policies;

use App\Models\User;
use App\Modules\Appointments\Infrastructure\Models\DoctorSchedule;

class DoctorSchedulePolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }

    public function view(User $user, DoctorSchedule $doctorSchedule): bool
    {
        return $user->ownsProvider($doctorSchedule->provider);
    }

    public function update(User $user, DoctorSchedule $doctorSchedule): bool
    {
        return $user->ownsProvider($doctorSchedule->provider);
    }
}
