<?php

namespace App\Modules\CarePlans\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanFoodItem;
use Illuminate\Support\Facades\DB;

class CarePlanFoodService
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function create(User $actor, CarePlan $plan, array $data): CarePlanFoodItem
    {
        return DB::transaction(function () use ($actor, $plan, $data): CarePlanFoodItem {
            $food = $plan->foodItems()->create($data);
            $this->auditLogService->log('care_plan_food.created', $food, $actor, metadata: ['care_plan_id' => $plan->id]);

            return $food->refresh();
        });
    }

    public function update(User $actor, CarePlanFoodItem $food, array $data): CarePlanFoodItem
    {
        return DB::transaction(function () use ($actor, $food, $data): CarePlanFoodItem {
            $before = $food->getAttributes();
            $food->fill($data)->save();
            $this->auditLogService->log('care_plan_food.updated', $food, $actor, before: $before, after: $food->getAttributes());

            return $food->refresh();
        });
    }

    public function delete(User $actor, CarePlanFoodItem $food): void
    {
        DB::transaction(function () use ($actor, $food): void {
            $before = $food->getAttributes();
            $food->delete();
            $this->auditLogService->log('care_plan_food.deleted', $food, $actor, before: $before, after: []);
        });
    }
}
