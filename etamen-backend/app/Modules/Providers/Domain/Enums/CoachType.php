<?php

namespace App\Modules\Providers\Domain\Enums;

enum CoachType: string
{
    case Fitness = 'fitness';
    case Nutrition = 'nutrition';
    case Rehab = 'rehab';
    case Bodybuilding = 'bodybuilding';
    case SportsPerformance = 'sports_performance';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
