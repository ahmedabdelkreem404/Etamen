<?php

namespace App\Modules\Health\Domain\Enums;

enum VitalSource: string
{
    case Manual = 'manual';
    case Imported = 'imported';
    case ProviderEntry = 'provider_entry';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
