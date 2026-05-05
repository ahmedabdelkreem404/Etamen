<?php

namespace App\Modules\CarePlans\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\CarePlans\Application\Services\CarePlanFoodService;
use App\Modules\CarePlans\Http\Requests\CarePlanFoodItemRequest;
use App\Modules\CarePlans\Http\Resources\CarePlanFoodItemResource;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanFoodItem;
use Illuminate\Http\Request;

class CarePlanFoodItemController extends ApiController
{
    public function __construct(private readonly CarePlanFoodService $foods) {}

    public function index(CarePlan $plan)
    {
        $this->authorize('view', $plan);

        return $this->success(CarePlanFoodItemResource::collection($plan->foodItems()->get()), 'Care plan food items.');
    }

    public function store(CarePlanFoodItemRequest $request, CarePlan $plan)
    {
        $this->authorize('manageStructure', $plan);

        return $this->success(new CarePlanFoodItemResource($this->foods->create($request->user(), $plan, $request->validated())), 'Care plan food item created.', 201);
    }

    public function update(CarePlanFoodItemRequest $request, CarePlan $plan, CarePlanFoodItem $food)
    {
        abort_unless((int) $food->care_plan_id === (int) $plan->id, 404);
        $this->authorize('update', $food);

        return $this->success(new CarePlanFoodItemResource($this->foods->update($request->user(), $food, $request->validated())), 'Care plan food item updated.');
    }

    public function destroy(Request $request, CarePlan $plan, CarePlanFoodItem $food)
    {
        abort_unless((int) $food->care_plan_id === (int) $plan->id, 404);
        $this->authorize('delete', $food);
        $this->foods->delete($request->user(), $food);

        return $this->success(null, 'Care plan food item deleted.');
    }
}
