<?php

namespace App\Modules\Health\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Health\Application\Services\HealthSummaryService;
use App\Modules\Health\Application\Services\HealthTrendService;
use App\Modules\Health\Application\Services\VitalRecordService;
use App\Modules\Health\Http\Requests\VitalRecordRequest;
use App\Modules\Health\Http\Requests\VitalTrendRequest;
use App\Modules\Health\Http\Resources\HealthSummaryResource;
use App\Modules\Health\Http\Resources\VitalRecordResource;
use App\Modules\Health\Http\Resources\VitalTrendResource;
use App\Modules\Health\Infrastructure\Models\VitalRecord;
use Illuminate\Http\Request;

class VitalRecordController extends ApiController
{
    public function __construct(
        private readonly VitalRecordService $vitals,
        private readonly HealthTrendService $trends,
        private readonly HealthSummaryService $summary,
    ) {}

    public function index(Request $request)
    {
        $records = VitalRecord::query()
            ->where('patient_user_id', $request->user()->id)
            ->when($request->query('vital_type'), fn ($query, $type) => $query->where('vital_type', $type))
            ->when($request->query('from'), fn ($query, $date) => $query->whereDate('measured_at', '>=', $date))
            ->when($request->query('to'), fn ($query, $date) => $query->whereDate('measured_at', '<=', $date))
            ->orderByDesc('measured_at')
            ->get();

        return $this->success(VitalRecordResource::collection($records), 'Vital records.');
    }

    public function store(VitalRecordRequest $request)
    {
        $record = $this->vitals->create($request->user(), $request->validated());

        return $this->success(new VitalRecordResource($record), 'Vital record created.', 201);
    }

    public function show(VitalRecord $vital)
    {
        $this->authorize('view', $vital);

        return $this->success(new VitalRecordResource($vital), 'Vital record details.');
    }

    public function update(VitalRecordRequest $request, VitalRecord $vital)
    {
        $this->authorize('update', $vital);
        $record = $this->vitals->update($request->user(), $vital, $request->validated());

        return $this->success(new VitalRecordResource($record), 'Vital record updated.');
    }

    public function destroy(Request $request, VitalRecord $vital)
    {
        $this->authorize('delete', $vital);
        $this->vitals->delete($request->user(), $vital);

        return $this->success(null, 'Vital record deleted.');
    }

    public function trends(VitalTrendRequest $request)
    {
        return $this->success(new VitalTrendResource($this->trends->trends($request->user(), $request->validated())), 'Vital trends.');
    }

    public function latest(Request $request)
    {
        return $this->success(VitalRecordResource::collection($this->trends->latest($request->user())), 'Latest vitals.');
    }

    public function summary(Request $request)
    {
        return $this->success(new HealthSummaryResource($this->summary->summary($request->user())), 'Health summary.');
    }
}
