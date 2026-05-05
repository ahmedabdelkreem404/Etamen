<?php

namespace App\Modules\Health\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class HealthRecordService
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function create(User $patient, string $modelClass, array $data, string $auditAction): Model
    {
        return DB::transaction(function () use ($patient, $modelClass, $data, $auditAction): Model {
            $record = $modelClass::query()->create([
                'patient_user_id' => $patient->id,
                ...$data,
            ]);

            $this->auditLogService->log($auditAction.'.created', $record, $patient);

            return $record->refresh();
        });
    }

    public function update(User $patient, Model $record, array $data, string $auditAction): Model
    {
        return DB::transaction(function () use ($patient, $record, $data, $auditAction): Model {
            $before = $record->getAttributes();
            $record->fill($data)->save();

            $this->auditLogService->log($auditAction.'.updated', $record, $patient, before: $before, after: $record->getAttributes());

            return $record->refresh();
        });
    }

    public function delete(User $patient, Model $record, string $auditAction): void
    {
        DB::transaction(function () use ($patient, $record, $auditAction): void {
            $before = $record->getAttributes();

            if (array_key_exists('is_active', $record->getAttributes())) {
                $record->forceFill(['is_active' => false])->save();
                $after = $record->getAttributes();
            } else {
                $record->delete();
                $after = [];
            }

            $this->auditLogService->log($auditAction.'.deleted', $record, $patient, before: $before, after: $after);
        });
    }
}
