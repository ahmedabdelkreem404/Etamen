<?php

namespace App\Modules\CarePlans\Domain\Enums;

enum CarePlanInstructionType: string
{
    case General = 'general';
    case Hydration = 'hydration';
    case Sleep = 'sleep';
    case Activity = 'activity';
    case Nutrition = 'nutrition';
    case Warning = 'warning';
    case ProviderNote = 'provider_note';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
