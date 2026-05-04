<?php

namespace App\Modules\Payments\Domain\Enums;

enum PaymentMethodType: string
{
    case Paymob = 'paymob';
    case ManualVodafoneCash = 'manual_vodafone_cash';
    case ManualInstapay = 'manual_instapay';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
