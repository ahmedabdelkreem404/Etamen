<?php

namespace App\Modules\Radiology\Domain\Enums;

enum RadiologyOrderStatus: string
{
    case PendingPayment = 'pending_payment';
    case PendingPaymentReview = 'pending_payment_review';
    case Paid = 'paid';
    case Accepted = 'accepted';
    case InProgress = 'in_progress';
    case ResultReady = 'result_ready';
    case Completed = 'completed';
    case CancelledByPatient = 'cancelled_by_patient';
    case CancelledByProvider = 'cancelled_by_provider';
    case Rejected = 'rejected';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
