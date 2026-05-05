<?php

namespace App\Modules\Pharmacies\Domain\Enums;

enum PharmacyOrderPaymentStatus: string
{
    case Unpaid = 'unpaid';
    case PendingPayment = 'pending_payment';
    case PendingPaymentReview = 'pending_payment_review';
    case Paid = 'paid';
    case Rejected = 'rejected';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
