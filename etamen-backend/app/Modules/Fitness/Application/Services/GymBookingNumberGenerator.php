<?php

namespace App\Modules\Fitness\Application\Services;

use App\Modules\Fitness\Infrastructure\Models\GymBooking;
use Illuminate\Support\Str;

class GymBookingNumberGenerator
{
    public function generate(): string
    {
        do {
            $number = 'GYM-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
        } while (GymBooking::query()->where('booking_number', $number)->exists());

        return $number;
    }
}
