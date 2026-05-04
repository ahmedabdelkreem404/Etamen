<?php

namespace App\Modules\Appointments\Policies;

use App\Models\User;
use App\Modules\Appointments\Infrastructure\Models\AppointmentSlot;

class AppointmentSlotPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }

    public function view(User $user, AppointmentSlot $appointmentSlot): bool
    {
        return $user->ownsProvider($appointmentSlot->provider);
    }
}
