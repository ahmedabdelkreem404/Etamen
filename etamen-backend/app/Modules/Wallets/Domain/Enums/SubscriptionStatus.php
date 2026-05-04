<?php

namespace App\Modules\Wallets\Domain\Enums;

enum SubscriptionStatus: string
{
    case Trialing = 'trialing';
    case Active = 'active';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case PendingPayment = 'pending_payment';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
