<?php

namespace App\Modules\Pharmacies\Domain\Enums;

enum PharmacyDeliveryMethod: string
{
    case Pickup = 'pickup';
    case Delivery = 'delivery';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
