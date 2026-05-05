<?php

namespace App\Modules\Medications\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Medications\Infrastructure\Models\MedicationReminder;
use App\Modules\Medications\Infrastructure\Models\MedicationReminderTime;
use Illuminate\Support\Facades\DB;

class MedicationReminderTimeService
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function create(User $patient, MedicationReminder $reminder, array $data): MedicationReminderTime
    {
        return DB::transaction(function () use ($patient, $reminder, $data): MedicationReminderTime {
            $time = $reminder->times()->create([
                'time_of_day' => $data['time_of_day'],
                'label' => $data['label'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);
            $this->auditLogService->log('medication_reminder_time.created', $time, $patient);

            return $time->refresh();
        });
    }

    public function update(User $patient, MedicationReminderTime $time, array $data): MedicationReminderTime
    {
        return DB::transaction(function () use ($patient, $time, $data): MedicationReminderTime {
            $before = $time->getAttributes();
            $time->fill($data)->save();
            $this->auditLogService->log('medication_reminder_time.updated', $time, $patient, before: $before, after: $time->getAttributes());

            return $time->refresh();
        });
    }

    public function deactivate(User $patient, MedicationReminderTime $time): MedicationReminderTime
    {
        return DB::transaction(function () use ($patient, $time): MedicationReminderTime {
            $before = $time->getAttributes();
            $time->forceFill(['is_active' => false])->save();
            $this->auditLogService->log('medication_reminder_time.deactivated', $time, $patient, before: $before, after: $time->getAttributes());

            return $time->refresh();
        });
    }
}
