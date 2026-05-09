<?php

namespace App\Modules\Fitness\Application\Services;

use App\Modules\Fitness\Infrastructure\Models\CoachBooking;
use Illuminate\Support\Str;

class CoachBookingNumberGenerator
{
    public function generate(): string
    {
        do {
            $number = 'COACH-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
        } while (CoachBooking::query()->where('booking_number', $number)->exists());

        return $number;
    }
}
