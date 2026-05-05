<?php

namespace App\Modules\Labs\Domain\Enums;

enum LabOrderPaymentStatus: string
{
    case Unpaid = 'unpaid';
    case PendingPayment = 'pending_payment';
    case PendingPaymentReview = 'pending_payment_review';
    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
