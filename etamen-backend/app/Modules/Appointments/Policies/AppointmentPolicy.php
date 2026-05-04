<?php

namespace App\Modules\Appointments\Policies;

use App\Models\User;
use App\Modules\Appointments\Infrastructure\Models\Appointment;

class AppointmentPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return (int) $appointment->patient_user_id === (int) $user->id
            || $user->ownsProvider($appointment->provider);
    }

    public function cancel(User $user, Appointment $appointment): bool
    {
        return (int) $appointment->patient_user_id === (int) $user->id;
    }

    public function doctorManage(User $user, Appointment $appointment): bool
    {
        return $user->ownsProvider($appointment->provider);
    }

    public function review(User $user, Appointment $appointment): bool
    {
        return (int) $appointment->patient_user_id === (int) $user->id;
    }
}
