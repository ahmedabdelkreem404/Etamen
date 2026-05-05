<?php

namespace App\Modules\Notifications\Domain\Enums;

enum SchedulerRunStatus: string
{
    case Started = 'started';
    case Completed = 'completed';
    case Failed = 'failed';
    case Skipped = 'skipped';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
