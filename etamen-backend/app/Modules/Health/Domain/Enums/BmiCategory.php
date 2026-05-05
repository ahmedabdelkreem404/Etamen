<?php

namespace App\Modules\Health\Domain\Enums;

enum BmiCategory: string
{
    case Underweight = 'underweight';
    case Normal = 'normal';
    case Overweight = 'overweight';
    case Obese = 'obese';
    case Unknown = 'unknown';
}
