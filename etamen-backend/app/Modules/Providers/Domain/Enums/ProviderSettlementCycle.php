<?php

namespace App\Modules\Providers\Domain\Enums;

enum ProviderSettlementCycle: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Biweekly = 'biweekly';
    case Monthly = 'monthly';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
