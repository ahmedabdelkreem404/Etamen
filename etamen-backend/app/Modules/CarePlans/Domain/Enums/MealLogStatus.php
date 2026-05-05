<?php

namespace App\Modules\CarePlans\Domain\Enums;

enum MealLogStatus: string
{
    case Followed = 'followed';
    case PartiallyFollowed = 'partially_followed';
    case Skipped = 'skipped';
    case Replaced = 'replaced';
    case ExtraMeal = 'extra_meal';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
