<?php

namespace App\Modules\Medications\Application\Services;

use App\Modules\Medications\Domain\Enums\MedicationNotificationChannel;
use App\Modules\Medications\Domain\Enums\MedicationNotificationStatus;
use App\Modules\Medications\Domain\Enums\MedicationNotificationType;
use App\Modules\Medications\Domain\Enums\MedicationReminderStatus;
use App\Modules\Medications\Infrastructure\Models\MedicationNotificationQueue;
use App\Modules\Medications\Infrastructure\Models\MedicationReminder;
use Carbon\CarbonImmutable;

class MedicationNotificationService
{
    public function __construct(private readonly MedicationScheduleService $scheduleService) {}

    public function queueUpcoming(int $hoursAhead = 24): int
    {
        $from = now()->toImmutable();
        $to = $from->addHours(max(1, min($hoursAhead, 168)));
        $created = 0;

        MedicationReminder::query()
            ->where('status', MedicationReminderStatus::Active->value)
            ->chunkById(100, function ($reminders) use ($from, $to, &$created): void {
                foreach ($reminders as $reminder) {
                    foreach ($this->scheduleService->occurrences($reminder, $from, $to) as $occurrence) {
                        if ($occurrence['scheduled_for']->gt($to)) {
                            continue;
                        }

                        $queue = MedicationNotificationQueue::query()->firstOrCreate([
                            'medication_reminder_id' => $reminder->id,
                            'patient_user_id' => $reminder->patient_user_id,
                            'scheduled_for' => $occurrence['scheduled_for']->toDateTimeString(),
                            'notification_type' => MedicationNotificationType::MedicationReminder,
                            'channel' => MedicationNotificationChannel::Local,
                        ], [
                            'status' => MedicationNotificationStatus::Pending,
                            'payload' => [
                                'medication_name' => $reminder->medication_name,
                                'safe_disclaimer' => 'تذكيرات الأدوية للتنظيم فقط وليست وصفة طبية أو نصيحة علاجية.',
                            ],
                        ]);

                        if ($queue->wasRecentlyCreated) {
                            $created++;
                        }
                    }
                }
            });

        $this->queueRefillsDue($from, $to, $created);

        return $created;
    }

    private function queueRefillsDue(CarbonImmutable $from, CarbonImmutable $to, int &$created): void
    {
        MedicationReminder::query()
            ->where('status', MedicationReminderStatus::Active->value)
            ->where('refill_enabled', true)
            ->whereBetween('refill_reminder_date', [$from->toDateString(), $to->toDateString()])
            ->each(function (MedicationReminder $reminder) use (&$created): void {
                $scheduledFor = CarbonImmutable::parse($reminder->refill_reminder_date)->setTime(9, 0);
                $queue = MedicationNotificationQueue::query()->firstOrCreate([
                    'medication_reminder_id' => $reminder->id,
                    'patient_user_id' => $reminder->patient_user_id,
                    'scheduled_for' => $scheduledFor->toDateTimeString(),
                    'notification_type' => MedicationNotificationType::RefillReminder,
                    'channel' => MedicationNotificationChannel::Local,
                ], [
                    'status' => MedicationNotificationStatus::Pending,
                    'payload' => ['medication_name' => $reminder->medication_name],
                ]);

                if ($queue->wasRecentlyCreated) {
                    $created++;
                }
            });
    }
}
