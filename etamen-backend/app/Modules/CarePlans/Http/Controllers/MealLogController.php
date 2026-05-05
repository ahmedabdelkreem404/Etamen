<?php

namespace App\Modules\CarePlans\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\CarePlans\Application\Services\MealLogService;
use App\Modules\CarePlans\Http\Requests\MealLogRequest;
use App\Modules\CarePlans\Http\Resources\MealLogResource;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use App\Modules\CarePlans\Infrastructure\Models\MealLog;
use Illuminate\Http\Request;

class MealLogController extends ApiController
{
    public function __construct(private readonly MealLogService $mealLogs) {}

    public function index(Request $request, CarePlan $plan)
    {
        $this->authorize('view', $plan);

        return $this->success(MealLogResource::collection($plan->mealLogs()->with('photo')->orderByDesc('logged_at')->limit($this->perPage($request))->get()), 'Meal logs.');
    }

    public function store(MealLogRequest $request, CarePlan $plan)
    {
        $this->authorize('track', $plan);
        $log = $this->mealLogs->create($request->user(), $plan, $request->validated(), $request->file('photo'));

        return $this->success(new MealLogResource($log), 'Meal log created.', 201);
    }

    public function show(CarePlan $plan, MealLog $log)
    {
        abort_unless((int) $log->care_plan_id === (int) $plan->id, 404);
        $this->authorize('view', $log);

        return $this->success(new MealLogResource($log->load('photo')), 'Meal log details.');
    }

    public function update(MealLogRequest $request, CarePlan $plan, MealLog $log)
    {
        abort_unless((int) $log->care_plan_id === (int) $plan->id, 404);
        $this->authorize('update', $log);
        $log = $this->mealLogs->update($request->user(), $log, $request->validated(), $request->file('photo'));

        return $this->success(new MealLogResource($log), 'Meal log updated.');
    }

    public function destroy(Request $request, CarePlan $plan, MealLog $log)
    {
        abort_unless((int) $log->care_plan_id === (int) $plan->id, 404);
        $this->authorize('delete', $log);
        $this->mealLogs->delete($request->user(), $log);

        return $this->success(null, 'Meal log deleted.');
    }
}
