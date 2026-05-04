<?php

namespace App\Modules\Appointments\Policies;

use App\Models\User;
use App\Modules\Appointments\Infrastructure\Models\DoctorScheduleDay;

class DoctorScheduleDayPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }

    public function update(User $user, DoctorScheduleDay $doctorScheduleDay): bool
    {
        return $user->ownsProvider($doctorScheduleDay->schedule->provider);
    }
}
