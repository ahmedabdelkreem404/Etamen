<?php

namespace App\Modules\Labs\Domain\Enums;

enum LabOrderItemType: string
{
    case Test = 'test';
    case Package = 'package';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
