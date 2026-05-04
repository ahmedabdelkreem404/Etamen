<?php

namespace App\Modules\Payments\Domain\Enums;

enum PaymentStatus: string
{
    case Draft = 'draft';
    case AwaitingMethod = 'awaiting_method';
    case AwaitingProof = 'awaiting_proof';
    case PendingReview = 'pending_review';
    case PendingGateway = 'pending_gateway';
    case Verified = 'verified';
    case Rejected = 'rejected';
    case Failed = 'failed';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
