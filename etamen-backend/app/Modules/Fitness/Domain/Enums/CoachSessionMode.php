<?php

namespace App\Modules\Fitness\Domain\Enums;

enum CoachSessionMode: string
{
    case Online = 'online';
    case Gym = 'gym';
    case Home = 'home';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
