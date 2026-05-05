<?php

namespace App\Modules\CarePlans\Domain\Enums;

enum CarePlanFoodCategory: string
{
    case Allowed = 'allowed';
    case Forbidden = 'forbidden';
    case Limited = 'limited';
    case Recommended = 'recommended';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
