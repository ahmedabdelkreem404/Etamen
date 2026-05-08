<?php

namespace App\Modules\Radiology\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Radiology\Http\Resources\RadiologyPreparationInstructionResource;
use App\Modules\Radiology\Http\Resources\RadiologyScanCategoryResource;
use App\Modules\Radiology\Http\Resources\RadiologyScanResource;
use App\Modules\Radiology\Infrastructure\Models\RadiologyPreparationInstruction;
use App\Modules\Radiology\Infrastructure\Models\RadiologyScan;
use App\Modules\Radiology\Infrastructure\Models\RadiologyScanCategory;
use Illuminate\Http\Request;

class PublicRadiologyCatalogController extends ApiController
{
    public function categories(Request $request)
    {
        $categories = RadiologyScanCategory::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name_en')
            ->limit($this->perPage($request, 100))
            ->get();

        return $this->success(RadiologyScanCategoryResource::collection($categories), 'Radiology scan categories.');
    }

    public function scans(Request $request)
    {
        $scans = RadiologyScan::query()
            ->publiclyVisible()
            ->with(['category', 'provider.branches.city', 'provider.branches.area', 'branch.city', 'branch.area'])
            ->when($request->integer('category_id'), fn ($query, $categoryId) => $query->where('radiology_scan_category_id', $categoryId))
            ->orderBy('name_en')
            ->limit($this->perPage($request))
            ->get();

        return $this->success(RadiologyScanResource::collection($scans), 'Radiology scans.');
    }

    public function instructions(Request $request)
    {
        $instructions = RadiologyPreparationInstruction::query()
            ->active()
            ->with('category')
            ->when($request->integer('category_id'), fn ($query, $categoryId) => $query->where('radiology_scan_category_id', $categoryId))
            ->when($request->integer('scan_id'), fn ($query, $scanId) => $query->where('radiology_scan_id', $scanId))
            ->orderBy('sort_order')
            ->limit($this->perPage($request, 100))
            ->get();

        return $this->success(RadiologyPreparationInstructionResource::collection($instructions), 'Radiology preparation instructions.');
    }
}
