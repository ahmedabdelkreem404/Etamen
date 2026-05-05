<?php

namespace App\Modules\Medications\Application\Jobs;

use App\Modules\Medications\Application\Services\MedicationNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class QueueMedicationReminderNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly int $hoursAhead = 24) {}

    public function handle(MedicationNotificationService $notifications): void
    {
        $notifications->queueUpcoming($this->hoursAhead);
    }
}
