<?php

namespace App\Modules\Medications\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Medications\Domain\Enums\MedicationRefillEventType;
use App\Modules\Medications\Infrastructure\Models\MedicationRefillEvent;
use App\Modules\Medications\Infrastructure\Models\MedicationReminder;
use Illuminate\Support\Facades\DB;

class MedicationRefillService
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function record(User $patient, MedicationReminder $reminder, MedicationRefillEventType $type, array $data = []): MedicationRefillEvent
    {
        return DB::transaction(function () use ($patient, $reminder, $type, $data): MedicationRefillEvent {
            $event = MedicationRefillEvent::query()->create([
                'medication_reminder_id' => $reminder->id,
                'patient_user_id' => $patient->id,
                'event_type' => $type,
                'event_date' => $data['event_date'] ?? now()->toDateString(),
                'notes' => $data['notes'] ?? null,
            ]);
            $this->auditLogService->log('medication_refill_event.created', $event, $patient);

            return $event->refresh();
        });
    }
}
