<?php

namespace App\Modules\Providers\Domain\Enums;

enum ProviderContractType: string
{
    case CommissionOnly = 'commission_only';
    case SubscriptionOnly = 'subscription_only';
    case Hybrid = 'hybrid';
    case Custom = 'custom';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
