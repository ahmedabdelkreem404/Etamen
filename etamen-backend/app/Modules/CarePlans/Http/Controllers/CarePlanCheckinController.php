<?php

namespace App\Modules\CarePlans\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\CarePlans\Application\Services\CarePlanCheckinService;
use App\Modules\CarePlans\Http\Requests\CarePlanCheckinRequest;
use App\Modules\CarePlans\Http\Resources\CarePlanCheckinResource;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanCheckin;

class CarePlanCheckinController extends ApiController
{
    public function __construct(private readonly CarePlanCheckinService $checkins) {}

    public function index(CarePlan $plan)
    {
        $this->authorize('view', $plan);

        return $this->success(CarePlanCheckinResource::collection($plan->checkins()->orderByDesc('checkin_date')->get()), 'Care plan check-ins.');
    }

    public function store(CarePlanCheckinRequest $request, CarePlan $plan)
    {
        $this->authorize('track', $plan);

        return $this->success(new CarePlanCheckinResource($this->checkins->record($request->user(), $plan, $request->validated())), 'Care plan check-in recorded.', 201);
    }

    public function update(CarePlanCheckinRequest $request, CarePlan $plan, CarePlanCheckin $checkin)
    {
        abort_unless((int) $checkin->care_plan_id === (int) $plan->id, 404);
        $this->authorize('update', $checkin);

        return $this->success(new CarePlanCheckinResource($this->checkins->update($request->user(), $checkin, $request->validated())), 'Care plan check-in updated.');
    }
}
