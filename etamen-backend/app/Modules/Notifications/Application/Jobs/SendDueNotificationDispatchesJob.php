<?php

namespace App\Modules\Notifications\Application\Jobs;

use App\Modules\Notifications\Application\Services\NotificationDispatchService;
use App\Modules\Notifications\Application\Services\SchedulerRunService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendDueNotificationDispatchesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly int $limit = 100) {}

    public function handle(NotificationDispatchService $dispatches, SchedulerRunService $runs): void
    {
        $run = $runs->start(self::class, ['limit' => $this->limit]);

        try {
            $result = $dispatches->processDue($this->limit);
            $runs->complete($run, $result['processed'], $result['failed']);
        } catch (Throwable $exception) {
            $runs->fail($run, $exception);
        }
    }
}
