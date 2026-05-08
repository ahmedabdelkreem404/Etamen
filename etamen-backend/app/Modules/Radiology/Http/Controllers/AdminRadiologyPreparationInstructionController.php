<?php

namespace App\Modules\Radiology\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Radiology\Http\Requests\RadiologyPreparationInstructionRequest;
use App\Modules\Radiology\Http\Resources\RadiologyPreparationInstructionResource;
use App\Modules\Radiology\Infrastructure\Models\RadiologyPreparationInstruction;
use Illuminate\Http\Request;

class AdminRadiologyPreparationInstructionController extends ApiController
{
    public function index(Request $request)
    {
        $instructions = RadiologyPreparationInstruction::query()
            ->with('category')
            ->when($request->integer('category_id'), fn ($query, $categoryId) => $query->where('radiology_scan_category_id', $categoryId))
            ->when($request->integer('scan_id'), fn ($query, $scanId) => $query->where('radiology_scan_id', $scanId))
            ->when($request->has('is_active'), fn ($query) => $query->where('is_active', $request->boolean('is_active')))
            ->orderBy('sort_order')
            ->get();

        return $this->success(RadiologyPreparationInstructionResource::collection($instructions), 'Radiology preparation instructions.');
    }

    public function store(RadiologyPreparationInstructionRequest $request)
    {
        $instruction = RadiologyPreparationInstruction::query()->create($request->validated());

        return $this->success(new RadiologyPreparationInstructionResource($instruction->load('category')), 'Radiology preparation instruction created.', 201);
    }

    public function update(RadiologyPreparationInstructionRequest $request, RadiologyPreparationInstruction $instruction)
    {
        $instruction->update($request->validated());

        return $this->success(new RadiologyPreparationInstructionResource($instruction->refresh()->load('category')), 'Radiology preparation instruction updated.');
    }

    public function destroy(RadiologyPreparationInstruction $instruction)
    {
        $instruction->update(['is_active' => false]);

        return $this->success(new RadiologyPreparationInstructionResource($instruction->refresh()->load('category')), 'Radiology preparation instruction deactivated.');
    }
}
