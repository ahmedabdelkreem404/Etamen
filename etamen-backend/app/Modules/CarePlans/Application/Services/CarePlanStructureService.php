<?php

namespace App\Modules\CarePlans\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanDay;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanMeal;
use Illuminate\Support\Facades\DB;

class CarePlanStructureService
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function createDay(User $actor, CarePlan $plan, array $data): CarePlanDay
    {
        return DB::transaction(function () use ($actor, $plan, $data): CarePlanDay {
            $day = $plan->days()->create($data);
            $this->auditLogService->log('care_plan_day.created', $day, $actor, metadata: ['care_plan_id' => $plan->id]);

            return $day->refresh();
        });
    }

    public function updateDay(User $actor, CarePlanDay $day, array $data): CarePlanDay
    {
        return DB::transaction(function () use ($actor, $day, $data): CarePlanDay {
            $before = $day->getAttributes();
            $day->fill($data)->save();
            $this->auditLogService->log('care_plan_day.updated', $day, $actor, before: $before, after: $day->getAttributes());

            return $day->refresh();
        });
    }

    public function createMeal(User $actor, CarePlanDay $day, array $data): CarePlanMeal
    {
        return DB::transaction(function () use ($actor, $day, $data): CarePlanMeal {
            $meal = $day->meals()->create($data);
            $this->auditLogService->log('care_plan_meal.created', $meal, $actor, metadata: ['care_plan_day_id' => $day->id]);

            return $meal->refresh();
        });
    }

    public function updateMeal(User $actor, CarePlanMeal $meal, array $data): CarePlanMeal
    {
        return DB::transaction(function () use ($actor, $meal, $data): CarePlanMeal {
            $before = $meal->getAttributes();
            $meal->fill($data)->save();
            $this->auditLogService->log('care_plan_meal.updated', $meal, $actor, before: $before, after: $meal->getAttributes());

            return $meal->refresh();
        });
    }

    public function deleteMeal(User $actor, CarePlanMeal $meal): void
    {
        DB::transaction(function () use ($actor, $meal): void {
            $before = $meal->getAttributes();
            $meal->delete();
            $this->auditLogService->log('care_plan_meal.deleted', $meal, $actor, before: $before, after: []);
        });
    }
}
