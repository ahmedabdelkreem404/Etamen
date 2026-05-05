<?php

namespace App\Modules\Notifications\Infrastructure\Models;

use App\Modules\Notifications\Domain\Enums\SchedulerRunStatus;
use Illuminate\Database\Eloquent\Model;

class SchedulerRun extends Model
{
    protected $fillable = [
        'job_name',
        'status',
        'started_at',
        'finished_at',
        'processed_count',
        'failed_count',
        'metadata',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'status' => SchedulerRunStatus::class,
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'processed_count' => 'integer',
            'failed_count' => 'integer',
            'metadata' => 'array',
        ];
    }
}
