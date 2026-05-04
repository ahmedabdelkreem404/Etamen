<?php

namespace App\Modules\Appointments\Application\Services;

use App\Modules\Appointments\Infrastructure\Models\Appointment;
use Illuminate\Support\Str;

class AppointmentNumberGenerator
{
    public function generate(): string
    {
        do {
            $number = 'APT-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
        } while (Appointment::query()->where('appointment_number', $number)->exists());

        return $number;
    }
}
