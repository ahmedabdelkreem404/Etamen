<?php

namespace App\Modules\Fitness\Domain\Enums;

enum CoachAvailabilityStatus: string
{
    case Available = 'available';
    case Booked = 'booked';
    case Blocked = 'blocked';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
