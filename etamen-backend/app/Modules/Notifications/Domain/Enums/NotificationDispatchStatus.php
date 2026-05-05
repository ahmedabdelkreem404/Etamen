<?php

namespace App\Modules\Notifications\Domain\Enums;

enum NotificationDispatchStatus: string
{
    case Pending = 'pending';
    case Queued = 'queued';
    case Sent = 'sent';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Skipped = 'skipped';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
