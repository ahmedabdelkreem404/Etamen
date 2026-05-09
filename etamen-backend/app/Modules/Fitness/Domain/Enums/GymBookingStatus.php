<?php

namespace App\Modules\Fitness\Domain\Enums;

enum GymBookingStatus: string
{
    case PendingPayment = 'pending_payment';
    case PendingPaymentReview = 'pending_payment_review';
    case Paid = 'paid';
    case Confirmed = 'confirmed';
    case Active = 'active';
    case Completed = 'completed';
    case CancelledByUser = 'cancelled_by_user';
    case CancelledByProvider = 'cancelled_by_provider';
    case Rejected = 'rejected';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
