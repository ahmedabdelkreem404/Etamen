<?php

namespace App\Modules\CarePlans\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\CarePlans\Application\Services\CarePlanStatusService;
use App\Modules\CarePlans\Domain\Enums\CarePlanStatus;
use App\Modules\CarePlans\Http\Resources\CarePlanResource;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use Illuminate\Http\Request;

class CarePlanStatusController extends ApiController
{
    public function __construct(private readonly CarePlanStatusService $statuses) {}

    public function activate(Request $request, CarePlan $plan)
    {
        $this->authorize('update', $plan);

        return $this->success(new CarePlanResource($this->statuses->transition($plan, CarePlanStatus::Active, $request->user())), 'Care plan activated.');
    }

    public function pause(Request $request, CarePlan $plan)
    {
        $this->authorize('update', $plan);

        return $this->success(new CarePlanResource($this->statuses->transition($plan, CarePlanStatus::Paused, $request->user())), 'Care plan paused.');
    }

    public function resume(Request $request, CarePlan $plan)
    {
        $this->authorize('update', $plan);

        return $this->success(new CarePlanResource($this->statuses->transition($plan, CarePlanStatus::Active, $request->user())), 'Care plan resumed.');
    }

    public function complete(Request $request, CarePlan $plan)
    {
        $this->authorize('update', $plan);

        return $this->success(new CarePlanResource($this->statuses->transition($plan, CarePlanStatus::Completed, $request->user())), 'Care plan completed.');
    }

    public function cancel(Request $request, CarePlan $plan)
    {
        $this->authorize('update', $plan);

        return $this->success(new CarePlanResource($this->statuses->transition($plan, CarePlanStatus::Cancelled, $request->user())), 'Care plan cancelled.');
    }
}
