<?php

namespace App\Modules\Payments\Domain\Enums;

enum PaymentProofStatus: string
{
    case Uploaded = 'uploaded';
    case PendingReview = 'pending_review';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
