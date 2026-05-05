<?php

namespace App\Modules\Medications\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Medications\Domain\Enums\MedicationReminderSource;
use App\Modules\Medications\Domain\Enums\MedicationReminderStatus;
use App\Modules\Medications\Infrastructure\Models\MedicationReminder;
use Illuminate\Support\Facades\DB;

class MedicationReminderService
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function create(User $patient, array $data): MedicationReminder
    {
        return DB::transaction(function () use ($patient, $data): MedicationReminder {
            $times = $data['times'] ?? [];
            unset($data['times'], $data['patient_user_id'], $data['source'], $data['status']);

            $reminder = MedicationReminder::query()->create([
                ...$data,
                'patient_user_id' => $patient->id,
                'status' => MedicationReminderStatus::Active,
                'source' => MedicationReminderSource::PatientEntered,
                'timezone' => $data['timezone'] ?? 'Africa/Cairo',
            ]);

            $this->syncTimes($reminder, $times);
            $this->auditLogService->log('medication_reminder.created', $reminder, $patient);

            return $reminder->refresh()->load('times');
        });
    }

    public function update(User $patient, MedicationReminder $reminder, array $data): MedicationReminder
    {
        return DB::transaction(function () use ($patient, $reminder, $data): MedicationReminder {
            $before = $reminder->getAttributes();
            $times = $data['times'] ?? null;
            unset($data['times'], $data['patient_user_id'], $data['source'], $data['status']);

            $reminder->fill($data)->save();

            if (is_array($times)) {
                $reminder->times()->delete();
                $this->syncTimes($reminder, $times);
            }

            $this->auditLogService->log('medication_reminder.updated', $reminder, $patient, before: $before, after: $reminder->getAttributes());

            return $reminder->refresh()->load('times');
        });
    }

    public function delete(User $patient, MedicationReminder $reminder): void
    {
        DB::transaction(function () use ($patient, $reminder): void {
            $before = $reminder->getAttributes();

            if ($reminder->logs()->exists()) {
                $reminder->forceFill(['status' => MedicationReminderStatus::Cancelled])->save();
                $this->auditLogService->log('medication_reminder.cancelled_on_delete', $reminder, $patient, before: $before, after: $reminder->getAttributes());

                return;
            }

            $reminder->delete();
            $this->auditLogService->log('medication_reminder.deleted', $reminder, $patient, before: $before, after: []);
        });
    }

    public function pause(User $patient, MedicationReminder $reminder): MedicationReminder
    {
        return $this->changeStatus($patient, $reminder, MedicationReminderStatus::Paused, 'medication_reminder.paused');
    }

    public function resume(User $patient, MedicationReminder $reminder): MedicationReminder
    {
        return $this->changeStatus($patient, $reminder, MedicationReminderStatus::Active, 'medication_reminder.resumed');
    }

    public function cancel(User $patient, MedicationReminder $reminder): MedicationReminder
    {
        return $this->changeStatus($patient, $reminder, MedicationReminderStatus::Cancelled, 'medication_reminder.cancelled');
    }

    private function changeStatus(User $patient, MedicationReminder $reminder, MedicationReminderStatus $status, string $action): MedicationReminder
    {
        return DB::transaction(function () use ($patient, $reminder, $status, $action): MedicationReminder {
            $before = $reminder->getAttributes();
            $reminder->forceFill(['status' => $status])->save();
            $this->auditLogService->log($action, $reminder, $patient, before: $before, after: $reminder->getAttributes());

            return $reminder->refresh()->load('times');
        });
    }

    private function syncTimes(MedicationReminder $reminder, array $times): void
    {
        foreach ($times as $time) {
            $reminder->times()->create([
                'time_of_day' => $time['time_of_day'],
                'label' => $time['label'] ?? null,
                'is_active' => $time['is_active'] ?? true,
            ]);
        }
    }
}
