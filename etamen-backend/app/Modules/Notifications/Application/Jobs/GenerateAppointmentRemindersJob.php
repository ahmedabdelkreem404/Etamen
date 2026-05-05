<?php

namespace App\Modules\Notifications\Application\Jobs;

use App\Modules\Notifications\Application\Services\NotificationSchedulerService;
use App\Modules\Notifications\Application\Services\SchedulerRunService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GenerateAppointmentRemindersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(NotificationSchedulerService $scheduler, SchedulerRunService $runs): void
    {
        $run = $runs->start(self::class);

        try {
            $runs->complete($run, $scheduler->generateAppointmentReminders());
        } catch (Throwable $exception) {
            $runs->fail($run, $exception);
        }
    }
}
