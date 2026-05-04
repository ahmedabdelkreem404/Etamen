<?php

namespace App\Modules\Appointments\Domain\Enums;

enum AppointmentStatus: string
{
    case Draft = 'draft';
    case PendingPayment = 'pending_payment';
    case PendingPaymentReview = 'pending_payment_review';
    case Confirmed = 'confirmed';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case CancelledByPatient = 'cancelled_by_patient';
    case CancelledByDoctor = 'cancelled_by_doctor';
    case Completed = 'completed';
    case NoShow = 'no_show';
    case Expired = 'expired';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
