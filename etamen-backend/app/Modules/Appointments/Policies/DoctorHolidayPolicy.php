<?php

namespace App\Modules\Appointments\Policies;

use App\Models\User;
use App\Modules\Appointments\Infrastructure\Models\DoctorHoliday;

class DoctorHolidayPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }

    public function view(User $user, DoctorHoliday $doctorHoliday): bool
    {
        return $user->ownsProvider($doctorHoliday->provider);
    }

    public function update(User $user, DoctorHoliday $doctorHoliday): bool
    {
        return $user->ownsProvider($doctorHoliday->provider);
    }
}
