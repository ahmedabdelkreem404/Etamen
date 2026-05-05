<?php

namespace App\Modules\Labs\Domain\Enums;

enum LabSampleCollectionMethod: string
{
    case BranchVisit = 'branch_visit';
    case HomeCollection = 'home_collection';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
