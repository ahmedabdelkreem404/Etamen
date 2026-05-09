<?php

namespace App\Modules\Fitness\Domain\Enums;

enum CoachBookingStatus: string
{
    case PendingPayment = 'pending_payment';
    case PendingPaymentReview = 'pending_payment_review';
    case Paid = 'paid';
    case Confirmed = 'confirmed';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case CancelledByUser = 'cancelled_by_user';
    case CancelledByCoach = 'cancelled_by_coach';
    case Rejected = 'rejected';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
