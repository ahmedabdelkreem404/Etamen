<?php

namespace App\Modules\Medications\Application\Jobs;

use App\Modules\Medications\Application\Services\MedicationLogService;
use App\Modules\Medications\Application\Services\MedicationScheduleService;
use App\Modules\Medications\Domain\Enums\MedicationFrequencyType;
use App\Modules\Medications\Domain\Enums\MedicationReminderStatus;
use App\Modules\Medications\Infrastructure\Models\MedicationReminder;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MarkMissedMedicationDosesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly int $graceHours = 2) {}

    public function handle(MedicationScheduleService $scheduleService, MedicationLogService $logService): void
    {
        $to = now()->toImmutable()->subHours($this->graceHours);
        $from = $to->subDay();

        MedicationReminder::query()
            ->where('status', MedicationReminderStatus::Active->value)
            ->where('frequency_type', '!=', MedicationFrequencyType::AsNeeded->value)
            ->chunkById(100, function ($reminders) use ($scheduleService, $logService, $from, $to): void {
                foreach ($reminders as $reminder) {
                    foreach ($scheduleService->occurrences($reminder, CarbonImmutable::parse($from), CarbonImmutable::parse($to)) as $occurrence) {
                        $logService->createMissed($reminder, $occurrence['scheduled_for']);
                    }
                }
            });
    }
}
