<?php

namespace App\Modules\CarePlans\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\CarePlans\Application\Services\CarePlanProgressService;
use App\Modules\CarePlans\Http\Requests\CarePlanProgressRequest;
use App\Modules\CarePlans\Http\Resources\CarePlanProgressResource;
use App\Modules\CarePlans\Http\Resources\CarePlanSummaryResource;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use Illuminate\Http\Request;

class CarePlanProgressController extends ApiController
{
    public function __construct(private readonly CarePlanProgressService $progress) {}

    public function progress(CarePlanProgressRequest $request, CarePlan $plan)
    {
        $this->authorize('view', $plan);

        return $this->success(new CarePlanProgressResource($this->progress->progress($plan, $request->validated())), 'Care plan progress.');
    }

    public function summary(Request $request)
    {
        return $this->success(new CarePlanSummaryResource($this->progress->summary($request->user())), 'Care plan summary.');
    }
}
