<?php

namespace App\Modules\Radiology\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Radiology\Http\Requests\RadiologyScanCategoryRequest;
use App\Modules\Radiology\Http\Resources\RadiologyScanCategoryResource;
use App\Modules\Radiology\Infrastructure\Models\RadiologyScanCategory;
use Illuminate\Http\Request;

class AdminRadiologyScanCategoryController extends ApiController
{
    public function index(Request $request)
    {
        $categories = RadiologyScanCategory::query()
            ->when($request->has('is_active'), fn ($query) => $query->where('is_active', $request->boolean('is_active')))
            ->orderBy('sort_order')
            ->orderBy('name_en')
            ->get();

        return $this->success(RadiologyScanCategoryResource::collection($categories), 'Admin radiology scan categories.');
    }

    public function store(RadiologyScanCategoryRequest $request)
    {
        $category = RadiologyScanCategory::query()->create($request->validated());

        return $this->success(new RadiologyScanCategoryResource($category), 'Radiology scan category created.', 201);
    }

    public function update(RadiologyScanCategoryRequest $request, RadiologyScanCategory $category)
    {
        $category->update($request->validated());

        return $this->success(new RadiologyScanCategoryResource($category->refresh()), 'Radiology scan category updated.');
    }

    public function destroy(RadiologyScanCategory $category)
    {
        $category->update(['is_active' => false]);

        return $this->success(new RadiologyScanCategoryResource($category->refresh()), 'Radiology scan category deactivated.');
    }
}
