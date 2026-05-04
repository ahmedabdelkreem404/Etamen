<?php

namespace App\Modules\Appointments\Policies;

use App\Models\User;
use App\Modules\Appointments\Infrastructure\Models\AppointmentReview;

class AppointmentReviewPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }

    public function view(User $user, AppointmentReview $appointmentReview): bool
    {
        return (int) $appointmentReview->patient_user_id === (int) $user->id
            || $user->ownsProvider($appointmentReview->doctorProfile->provider);
    }
}
