<?php

namespace App\Modules\CarePlans\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\CarePlans\Application\Services\CarePlanInstructionService;
use App\Modules\CarePlans\Http\Requests\CarePlanInstructionRequest;
use App\Modules\CarePlans\Http\Resources\CarePlanInstructionResource;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanInstruction;
use Illuminate\Http\Request;

class CarePlanInstructionController extends ApiController
{
    public function __construct(private readonly CarePlanInstructionService $instructions) {}

    public function index(CarePlan $plan)
    {
        $this->authorize('view', $plan);

        return $this->success(CarePlanInstructionResource::collection($plan->instructions()->get()), 'Care plan instructions.');
    }

    public function store(CarePlanInstructionRequest $request, CarePlan $plan)
    {
        $this->authorize('manageStructure', $plan);

        return $this->success(new CarePlanInstructionResource($this->instructions->create($request->user(), $plan, $request->validated())), 'Care plan instruction created.', 201);
    }

    public function update(CarePlanInstructionRequest $request, CarePlan $plan, CarePlanInstruction $instruction)
    {
        abort_unless((int) $instruction->care_plan_id === (int) $plan->id, 404);
        $this->authorize('update', $instruction);

        return $this->success(new CarePlanInstructionResource($this->instructions->update($request->user(), $instruction, $request->validated())), 'Care plan instruction updated.');
    }

    public function destroy(Request $request, CarePlan $plan, CarePlanInstruction $instruction)
    {
        abort_unless((int) $instruction->care_plan_id === (int) $plan->id, 404);
        $this->authorize('delete', $instruction);
        $this->instructions->delete($request->user(), $instruction);

        return $this->success(null, 'Care plan instruction deleted.');
    }
}
