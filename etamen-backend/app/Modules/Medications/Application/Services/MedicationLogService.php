<?php

namespace App\Modules\Medications\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Medications\Domain\Enums\MedicationLogAction;
use App\Modules\Medications\Domain\Enums\MedicationReminderStatus;
use App\Modules\Medications\Infrastructure\Models\MedicationLog;
use App\Modules\Medications\Infrastructure\Models\MedicationReminder;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MedicationLogService
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function record(User $patient, MedicationReminder $reminder, array $data, ?MedicationLogAction $forcedAction = null): MedicationLog
    {
        return DB::transaction(function () use ($patient, $reminder, $data, $forcedAction): MedicationLog {
            $this->ensureLoggable($reminder);
            $action = $forcedAction ?? MedicationLogAction::from($data['action']);

            if (! in_array($action, [MedicationLogAction::Taken, MedicationLogAction::Skipped], true)) {
                throw ValidationException::withMessages(['action' => 'Only taken or skipped can be recorded from the API.']);
            }

            $scheduledFor = CarbonImmutable::parse($data['scheduled_for'] ?? $data['taken_at'] ?? now());
            $this->ensureWithinReminderDates($reminder, $scheduledFor);

            $attributes = [
                'medication_reminder_id' => $reminder->id,
                'scheduled_for' => $scheduledFor->toDateTimeString(),
            ];
            $payload = [
                'patient_user_id' => $patient->id,
                'action' => $action,
                'taken_at' => $action === MedicationLogAction::Taken
                    ? ($data['taken_at'] ?? now()->toDateTimeString())
                    : null,
                'notes' => $data['notes'] ?? null,
                'metadata' => $data['metadata'] ?? null,
            ];

            $log = MedicationLog::query()->where($attributes)->first();
            $before = $log?->getAttributes();
            $log = MedicationLog::query()->updateOrCreate($attributes, $payload);

            $this->auditLogService->log(
                $before ? 'medication_log.updated' : 'medication_log.created',
                $log,
                $patient,
                before: $before,
                after: $log->getAttributes(),
            );

            return $log->refresh();
        });
    }

    public function update(User $patient, MedicationLog $log, array $data): MedicationLog
    {
        return DB::transaction(function () use ($patient, $log, $data): MedicationLog {
            $reminder = $log->reminder;
            $this->ensureLoggable($reminder);
            $action = MedicationLogAction::from($data['action']);
            $scheduledFor = CarbonImmutable::parse($data['scheduled_for']);
            $this->ensureWithinReminderDates($reminder, $scheduledFor);

            $before = $log->getAttributes();
            $log->fill([
                'scheduled_for' => $scheduledFor,
                'action' => $action,
                'taken_at' => $action === MedicationLogAction::Taken ? ($data['taken_at'] ?? now()) : null,
                'notes' => $data['notes'] ?? null,
                'metadata' => $data['metadata'] ?? null,
            ])->save();
            $this->auditLogService->log('medication_log.updated', $log, $patient, before: $before, after: $log->getAttributes());

            return $log->refresh();
        });
    }

    public function createMissed(MedicationReminder $reminder, CarbonImmutable $scheduledFor): ?MedicationLog
    {
        if ($reminder->logs()->where('scheduled_for', $scheduledFor->toDateTimeString())->exists()) {
            return null;
        }

        return MedicationLog::query()->create([
            'medication_reminder_id' => $reminder->id,
            'patient_user_id' => $reminder->patient_user_id,
            'scheduled_for' => $scheduledFor->toDateTimeString(),
            'action' => MedicationLogAction::Missed,
        ]);
    }

    private function ensureLoggable(MedicationReminder $reminder): void
    {
        if ($reminder->status !== MedicationReminderStatus::Active) {
            throw ValidationException::withMessages(['reminder' => 'Medication reminder is not active.']);
        }
    }

    private function ensureWithinReminderDates(MedicationReminder $reminder, CarbonImmutable $scheduledFor): void
    {
        if ($scheduledFor->lt(CarbonImmutable::parse($reminder->start_date)->startOfDay())) {
            throw ValidationException::withMessages(['scheduled_for' => 'Scheduled time is before reminder start date.']);
        }

        if ($reminder->end_date && $scheduledFor->gt(CarbonImmutable::parse($reminder->end_date)->endOfDay())) {
            throw ValidationException::withMessages(['scheduled_for' => 'Scheduled time is after reminder end date.']);
        }
    }
}
