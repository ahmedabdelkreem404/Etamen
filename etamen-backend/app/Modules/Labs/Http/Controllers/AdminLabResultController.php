<?php

namespace App\Modules\Labs\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Labs\Http\Resources\LabResultResource;
use App\Modules\Labs\Infrastructure\Models\LabResult;

class AdminLabResultController extends ApiController
{
    public function index()
    {
        $results = LabResult::query()
            ->with(['order.lab', 'order.patient', 'file'])
            ->latest('id')
            ->get();

        return $this->success(LabResultResource::collection($results), 'Admin lab results.');
    }

    public function show(LabResult $result)
    {
        return $this->success(new LabResultResource($result->load(['order.lab', 'order.patient', 'file'])), 'Admin lab result details.');
    }
}
