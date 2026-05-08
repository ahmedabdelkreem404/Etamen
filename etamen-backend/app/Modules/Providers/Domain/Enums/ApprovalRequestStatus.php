<?php

namespace App\Modules\Providers\Domain\Enums;

enum ApprovalRequestStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case NeedsChanges = 'needs_changes';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
