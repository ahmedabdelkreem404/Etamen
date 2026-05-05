<?php

namespace App\Modules\CarePlans\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\CarePlans\Application\Services\CarePlanStructureService;
use App\Modules\CarePlans\Http\Requests\CarePlanMealRequest;
use App\Modules\CarePlans\Http\Resources\CarePlanMealResource;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanDay;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanMeal;
use Illuminate\Http\Request;

class CarePlanMealController extends ApiController
{
    public function __construct(private readonly CarePlanStructureService $structure) {}

    public function index(CarePlan $plan)
    {
        $this->authorize('view', $plan);
        $meals = CarePlanMeal::query()
            ->whereHas('day', fn ($query) => $query->where('care_plan_id', $plan->id))
            ->orderBy('sort_order')
            ->get();

        return $this->success(CarePlanMealResource::collection($meals), 'Care plan meals.');
    }

    public function store(CarePlanMealRequest $request, CarePlan $plan)
    {
        $this->authorize('manageStructure', $plan);
        $day = CarePlanDay::query()->whereKey($request->validated('care_plan_day_id'))->where('care_plan_id', $plan->id)->firstOrFail();
        $data = $request->validated();
        unset($data['care_plan_day_id']);

        return $this->success(new CarePlanMealResource($this->structure->createMeal($request->user(), $day, $data)), 'Care plan meal created.', 201);
    }

    public function update(CarePlanMealRequest $request, CarePlan $plan, CarePlanMeal $meal)
    {
        abort_unless((int) $meal->day->care_plan_id === (int) $plan->id, 404);
        $this->authorize('update', $meal);
        $day = CarePlanDay::query()->whereKey($request->validated('care_plan_day_id'))->where('care_plan_id', $plan->id)->firstOrFail();
        $data = $request->validated();
        unset($data['care_plan_day_id']);
        $data['care_plan_day_id'] = $day->id;

        return $this->success(new CarePlanMealResource($this->structure->updateMeal($request->user(), $meal, $data)), 'Care plan meal updated.');
    }

    public function destroy(Request $request, CarePlan $plan, CarePlanMeal $meal)
    {
        abort_unless((int) $meal->day->care_plan_id === (int) $plan->id, 404);
        $this->authorize('delete', $meal);
        $this->structure->deleteMeal($request->user(), $meal);

        return $this->success(null, 'Care plan meal deleted.');
    }
}
