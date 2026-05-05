<?php

namespace App\Modules\Health\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Health\Application\Services\HealthRecordService;
use App\Modules\Health\Http\Requests\HealthGoalRequest;
use App\Modules\Health\Http\Resources\HealthGoalResource;
use App\Modules\Health\Infrastructure\Models\HealthGoal;
use Illuminate\Http\Request;

class HealthGoalController extends ApiController
{
    public function __construct(private readonly HealthRecordService $records) {}

    public function index(Request $request)
    {
        return $this->success(HealthGoalResource::collection(
            HealthGoal::query()->where('patient_user_id', $request->user()->id)->orderByDesc('id')->get()
        ), 'Health goals.');
    }

    public function store(HealthGoalRequest $request)
    {
        $record = $this->records->create($request->user(), HealthGoal::class, $request->validated(), 'health.goal');

        return $this->success(new HealthGoalResource($record), 'Health goal created.', 201);
    }

    public function update(HealthGoalRequest $request, HealthGoal $goal)
    {
        $this->authorize('update', $goal);
        $record = $this->records->update($request->user(), $goal, $request->validated(), 'health.goal');

        return $this->success(new HealthGoalResource($record), 'Health goal updated.');
    }

    public function destroy(Request $request, HealthGoal $goal)
    {
        $this->authorize('delete', $goal);
        $this->records->delete($request->user(), $goal, 'health.goal');

        return $this->success(null, 'Health goal deleted.');
    }
}
