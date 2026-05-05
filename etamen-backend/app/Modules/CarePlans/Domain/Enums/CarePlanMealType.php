<?php

namespace App\Modules\CarePlans\Domain\Enums;

enum CarePlanMealType: string
{
    case Breakfast = 'breakfast';
    case Snack1 = 'snack_1';
    case Lunch = 'lunch';
    case Snack2 = 'snack_2';
    case Dinner = 'dinner';
    case Snack3 = 'snack_3';
    case Other = 'other';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
