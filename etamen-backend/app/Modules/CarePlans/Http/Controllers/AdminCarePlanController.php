<?php

namespace App\Modules\CarePlans\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\CarePlans\Application\Services\CarePlanService;
use App\Modules\CarePlans\Http\Requests\AdminCarePlanRequest;
use App\Modules\CarePlans\Http\Resources\CarePlanCheckinResource;
use App\Modules\CarePlans\Http\Resources\CarePlanResource;
use App\Modules\CarePlans\Http\Resources\MealLogResource;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanCheckin;
use App\Modules\CarePlans\Infrastructure\Models\MealLog;
use Illuminate\Http\Request;

class AdminCarePlanController extends ApiController
{
    public function __construct(private readonly CarePlanService $carePlans) {}

    public function index(Request $request)
    {
        $plans = CarePlan::query()
            ->when($request->query('patient_user_id'), fn ($query, $id) => $query->where('patient_user_id', $id))
            ->when($request->query('provider_id'), fn ($query, $id) => $query->where('provider_id', $id))
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('plan_type'), fn ($query, $type) => $query->where('plan_type', $type))
            ->withCount(['checkins', 'mealLogs'])
            ->orderByDesc('id')
            ->get();

        return $this->success(CarePlanResource::collection($plans), 'Care plans.');
    }

    public function store(AdminCarePlanRequest $request)
    {
        $plan = $this->carePlans->createByAdmin($request->user(), $request->validated());

        return $this->success(new CarePlanResource($plan), 'Admin care plan created.', 201);
    }

    public function show(CarePlan $plan)
    {
        return $this->success(new CarePlanResource($plan->load(['days.meals', 'foodItems', 'instructions'])), 'Care plan details.');
    }

    public function update(AdminCarePlanRequest $request, CarePlan $plan)
    {
        return $this->success(new CarePlanResource($this->carePlans->update($request->user(), $plan, $request->validated())), 'Care plan updated.');
    }

    public function checkins(Request $request)
    {
        $checkins = CarePlanCheckin::query()
            ->when($request->query('patient_user_id'), fn ($query, $id) => $query->where('patient_user_id', $id))
            ->orderByDesc('checkin_date')
            ->get();

        return $this->success(CarePlanCheckinResource::collection($checkins), 'Care plan check-ins.');
    }

    public function mealLogs(Request $request)
    {
        $logs = MealLog::query()
            ->with('photo')
            ->when($request->query('patient_user_id'), fn ($query, $id) => $query->where('patient_user_id', $id))
            ->orderByDesc('logged_at')
            ->get();

        return $this->success(MealLogResource::collection($logs), 'Meal logs.');
    }
}
