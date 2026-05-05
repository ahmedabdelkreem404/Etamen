<?php

namespace App\Modules\CarePlans\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\CarePlans\Application\Services\CarePlanStructureService;
use App\Modules\CarePlans\Http\Requests\CarePlanDayRequest;
use App\Modules\CarePlans\Http\Resources\CarePlanDayResource;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanDay;

class CarePlanDayController extends ApiController
{
    public function __construct(private readonly CarePlanStructureService $structure) {}

    public function index(CarePlan $plan)
    {
        $this->authorize('view', $plan);

        return $this->success(CarePlanDayResource::collection($plan->days()->with('meals')->get()), 'Care plan days.');
    }

    public function store(CarePlanDayRequest $request, CarePlan $plan)
    {
        $this->authorize('manageStructure', $plan);
        $day = $this->structure->createDay($request->user(), $plan, $request->validated());

        return $this->success(new CarePlanDayResource($day), 'Care plan day created.', 201);
    }

    public function update(CarePlanDayRequest $request, CarePlan $plan, CarePlanDay $day)
    {
        abort_unless((int) $day->care_plan_id === (int) $plan->id, 404);
        $this->authorize('update', $day);

        return $this->success(new CarePlanDayResource($this->structure->updateDay($request->user(), $day, $request->validated())), 'Care plan day updated.');
    }
}
