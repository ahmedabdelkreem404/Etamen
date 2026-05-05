<?php

namespace App\Modules\CarePlans\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\CarePlans\Application\Services\CarePlanAccessService;
use App\Modules\CarePlans\Application\Services\CarePlanService;
use App\Modules\CarePlans\Http\Requests\CarePlanRequest;
use App\Modules\CarePlans\Http\Requests\ProviderAssignCarePlanRequest;
use App\Modules\CarePlans\Http\Resources\CarePlanResource;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use Illuminate\Http\Request;

class ProviderCarePlanController extends ApiController
{
    public function __construct(
        private readonly CarePlanAccessService $accessService,
        private readonly CarePlanService $carePlans,
    ) {}

    public function index(Request $request)
    {
        $provider = $this->accessService->currentDoctorProviderFor($request->user());
        $plans = CarePlan::query()
            ->where('provider_id', $provider->id)
            ->withCount(['checkins', 'mealLogs'])
            ->orderByDesc('id')
            ->get();

        return $this->success(CarePlanResource::collection($plans), 'Provider care plans.');
    }

    public function assign(ProviderAssignCarePlanRequest $request)
    {
        $plan = $this->carePlans->assignByProvider($request->user(), $request->validated());

        return $this->success(new CarePlanResource($plan), 'Care plan assigned.', 201);
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
}
