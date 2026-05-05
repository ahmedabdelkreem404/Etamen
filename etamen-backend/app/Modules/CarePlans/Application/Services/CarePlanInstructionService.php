<?php

namespace App\Modules\CarePlans\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanInstruction;
use Illuminate\Support\Facades\DB;

class CarePlanInstructionService
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function create(User $actor, CarePlan $plan, array $data): CarePlanInstruction
    {
        return DB::transaction(function () use ($actor, $plan, $data): CarePlanInstruction {
            $instruction = $plan->instructions()->create($data);
            $this->auditLogService->log('care_plan_instruction.created', $instruction, $actor, metadata: ['care_plan_id' => $plan->id]);

            return $instruction->refresh();
        });
    }

    public function update(User $actor, CarePlanInstruction $instruction, array $data): CarePlanInstruction
    {
        return DB::transaction(function () use ($actor, $instruction, $data): CarePlanInstruction {
            $before = $instruction->getAttributes();
            $instruction->fill($data)->save();
            $this->auditLogService->log('care_plan_instruction.updated', $instruction, $actor, before: $before, after: $instruction->getAttributes());

            return $instruction->refresh();
        });
    }

    public function delete(User $actor, CarePlanInstruction $instruction): void
    {
        DB::transaction(function () use ($actor, $instruction): void {
            $before = $instruction->getAttributes();
            $instruction->delete();
            $this->auditLogService->log('care_plan_instruction.deleted', $instruction, $actor, before: $before, after: []);
        });
    }
}
