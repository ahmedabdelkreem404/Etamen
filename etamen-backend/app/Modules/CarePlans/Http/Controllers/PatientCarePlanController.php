<?php

namespace App\Modules\CarePlans\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\CarePlans\Application\Services\CarePlanService;
use App\Modules\CarePlans\Http\Requests\CarePlanRequest;
use App\Modules\CarePlans\Http\Resources\CarePlanResource;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use Illuminate\Http\Request;

class PatientCarePlanController extends ApiController
{
    public function __construct(private readonly CarePlanService $carePlans) {}

    public function index(Request $request)
    {
        $plans = CarePlan::query()
            ->where('patient_user_id', $request->user()->id)
            ->withCount(['checkins', 'mealLogs'])
            ->orderByDesc('id')
            ->get();

        return $this->success(CarePlanResource::collection($plans), 'Care plans.');
    }

    public function store(CarePlanRequest $request)
    {
        $plan = $this->carePlans->createPatientPlan($request->user(), $request->validated());

        return $this->success(new CarePlanResource($plan), 'Care plan created.', 201);
    }

    public function show(CarePlan $plan)
    {
        $this->authorize('view', $plan);

        return $this->success(new CarePlanResource($plan->load(['days.meals', 'foodItems', 'instructions'])), 'Care plan details.');
    }

    public function update(CarePlanRequest $request, CarePlan $plan)
    {
        $this->authorize('update', $plan);

        return $this->success(new CarePlanResource($this->carePlans->update($request->user(), $plan, $request->validated())), 'Care plan updated.');
    }

    public function destroy(Request $request, CarePlan $plan)
    {
        $this->authorize('delete', $plan);
        $this->carePlans->delete($request->user(), $plan);

        return $this->success(null, 'Care plan deleted.');
    }
}
