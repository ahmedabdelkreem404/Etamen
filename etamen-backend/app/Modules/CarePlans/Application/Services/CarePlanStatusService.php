<?php

namespace App\Modules\CarePlans\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\CarePlans\Domain\Enums\CarePlanStatus;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CarePlanStatusService
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function transition(CarePlan $plan, CarePlanStatus $to, ?User $actor): CarePlan
    {
        return DB::transaction(function () use ($plan, $to, $actor): CarePlan {
            $plan = CarePlan::query()->whereKey($plan->id)->lockForUpdate()->firstOrFail();
            $from = $plan->status;

            if ($from === $to) {
                return $plan->refresh();
            }

            if (! in_array($to, $this->allowedTo($from), true)) {
                throw ValidationException::withMessages([
                    'status' => ['This care plan cannot move to the requested status.'],
                ]);
            }

            $before = $plan->getAttributes();
            $plan->forceFill(['status' => $to])->save();

            $this->auditLogService->log('care_plan.status_changed', $plan, $actor, before: $before, after: $plan->getAttributes(), metadata: [
                'from_status' => $from->value,
                'to_status' => $to->value,
            ]);

            return $plan->refresh();
        });
    }

    private function allowedTo(CarePlanStatus $from): array
    {
        return match ($from) {
            CarePlanStatus::Draft => [CarePlanStatus::Active],
            CarePlanStatus::Active => [CarePlanStatus::Paused, CarePlanStatus::Completed, CarePlanStatus::Cancelled],
            CarePlanStatus::Paused => [CarePlanStatus::Active, CarePlanStatus::Cancelled],
            default => [],
        };
    }
}
