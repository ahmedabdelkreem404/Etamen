<?php

namespace App\Modules\Notifications\Application\Services;

use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Notifications\Domain\Enums\SchedulerRunStatus;
use App\Modules\Notifications\Infrastructure\Models\SchedulerRun;
use Throwable;

class SchedulerRunService
{
    public function __construct(private readonly AuditLogService $auditLogs) {}

    public function start(string $jobName, array $metadata = []): SchedulerRun
    {
        return SchedulerRun::query()->create([
            'job_name' => $jobName,
            'status' => SchedulerRunStatus::Started,
            'started_at' => now(),
            'metadata' => $metadata,
        ]);
    }

    public function complete(SchedulerRun $run, int $processed = 0, int $failed = 0, array $metadata = []): SchedulerRun
    {
        $run->forceFill([
            'status' => SchedulerRunStatus::Completed,
            'finished_at' => now(),
            'processed_count' => $processed,
            'failed_count' => $failed,
            'metadata' => array_merge($run->metadata ?? [], $metadata),
        ])->save();

        return $run->refresh();
    }

    public function fail(SchedulerRun $run, Throwable $exception, int $processed = 0, int $failed = 1): SchedulerRun
    {
        $run->forceFill([
            'status' => SchedulerRunStatus::Failed,
            'finished_at' => now(),
            'processed_count' => $processed,
            'failed_count' => $failed,
            'error_message' => str($exception->getMessage())->limit(1000)->toString(),
        ])->save();

        $this->auditLogs->log('scheduler_run.failed', $run, metadata: [
            'job_name' => $run->job_name,
            'error' => $run->error_message,
        ]);

        return $run->refresh();
    }
}
