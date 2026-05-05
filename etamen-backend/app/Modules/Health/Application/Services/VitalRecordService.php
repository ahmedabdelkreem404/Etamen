<?php

namespace App\Modules\Health\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Health\Domain\Enums\VitalSource;
use App\Modules\Health\Domain\Enums\VitalType;
use App\Modules\Health\Infrastructure\Models\VitalRecord;
use Illuminate\Support\Facades\DB;

class VitalRecordService
{
    public function __construct(
        private readonly VitalFlagService $flagService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function create(User $patient, array $data): VitalRecord
    {
        return DB::transaction(function () use ($patient, $data): VitalRecord {
            $record = VitalRecord::query()->create($this->payload($patient, $data));
            $this->auditLogService->log('vital_record.created', $record, $patient);

            return $record->refresh();
        });
    }

    public function update(User $patient, VitalRecord $record, array $data): VitalRecord
    {
        return DB::transaction(function () use ($patient, $record, $data): VitalRecord {
            $before = $record->getAttributes();
            $record->fill($this->payload($patient, $data, includePatient: false))->save();
            $this->auditLogService->log('vital_record.updated', $record, $patient, before: $before, after: $record->getAttributes());

            return $record->refresh();
        });
    }

    public function delete(User $patient, VitalRecord $record): void
    {
        DB::transaction(function () use ($patient, $record): void {
            $before = $record->getAttributes();
            $record->delete();
            $this->auditLogService->log('vital_record.deleted', $record, $patient, before: $before, after: []);
        });
    }

    private function payload(User $patient, array $data, bool $includePatient = true): array
    {
        $type = VitalType::from($data['vital_type']);
        $metadata = $data['metadata'] ?? [];
        $value = array_key_exists('value_decimal', $data) ? (float) $data['value_decimal'] : null;
        $secondary = array_key_exists('value_secondary_decimal', $data) ? (float) $data['value_secondary_decimal'] : null;

        return [
            ...($includePatient ? ['patient_user_id' => $patient->id] : []),
            'vital_type' => $type,
            'measured_at' => $data['measured_at'],
            'value_decimal' => $data['value_decimal'] ?? null,
            'value_secondary_decimal' => $data['value_secondary_decimal'] ?? null,
            'unit' => $this->flagService->unitFor($type),
            'source' => VitalSource::Manual,
            'flag' => $this->flagService->flag($type, $value, $secondary, $metadata),
            'notes' => $data['notes'] ?? null,
            'metadata' => $metadata,
        ];
    }
}
