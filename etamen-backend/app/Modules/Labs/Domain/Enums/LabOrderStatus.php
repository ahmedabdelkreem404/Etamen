<?php

namespace App\Modules\Labs\Domain\Enums;

enum LabOrderStatus: string
{
    case LabReview = 'lab_review';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case AwaitingPayment = 'awaiting_payment';
    case Paid = 'paid';
    case SampleScheduled = 'sample_scheduled';
    case SampleCollected = 'sample_collected';
    case Processing = 'processing';
    case ResultReady = 'result_ready';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
