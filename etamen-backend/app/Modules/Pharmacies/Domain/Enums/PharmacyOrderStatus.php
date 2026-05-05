<?php

namespace App\Modules\Pharmacies\Domain\Enums;

enum PharmacyOrderStatus: string
{
    case Pending = 'pending';
    case PharmacyReview = 'pharmacy_review';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case AwaitingPayment = 'awaiting_payment';
    case Paid = 'paid';
    case Preparing = 'preparing';
    case ReadyForPickup = 'ready_for_pickup';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
