<?php

namespace App\Modules\Radiology\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Radiology\Application\Services\RadiologyAccessService;
use App\Modules\Radiology\Application\Services\RadiologyScanService;
use App\Modules\Radiology\Http\Requests\ProviderRadiologyScanRequest;
use App\Modules\Radiology\Http\Resources\RadiologyScanResource;
use App\Modules\Radiology\Infrastructure\Models\RadiologyScan;
use Illuminate\Http\Request;

class ProviderRadiologyScanController extends ApiController
{
    public function __construct(
        private readonly RadiologyAccessService $accessService,
        private readonly RadiologyScanService $scanService,
    ) {}

    public function index(Request $request)
    {
        $provider = $this->accessService->currentRadiologyFor($request->user());
        $scans = RadiologyScan::query()
            ->where('provider_id', $provider->id)
            ->with(['category', 'branch'])
            ->orderByDesc('id')
            ->get();

        return $this->success(RadiologyScanResource::collection($scans), 'Provider radiology scans.');
    }

    public function store(ProviderRadiologyScanRequest $request)
    {
        $scan = $this->scanService->createForProvider($request->user(), $request->validated());

        return $this->success(new RadiologyScanResource($scan), 'Radiology scan created.', 201);
    }

    public function show(RadiologyScan $scan)
    {
        $this->authorize('view', $scan);

        return $this->success(new RadiologyScanResource($scan->load(['category', 'branch'])), 'Radiology scan details.');
    }

    public function update(ProviderRadiologyScanRequest $request, RadiologyScan $scan)
    {
        $this->authorize('update', $scan);

        $scan = $this->scanService->update($request->user(), $scan, $request->validated());

        return $this->success(new RadiologyScanResource($scan), 'Radiology scan updated.');
    }

    public function destroy(Request $request, RadiologyScan $scan)
    {
        $this->authorize('delete', $scan);

        $scan = $this->scanService->deactivate($request->user(), $scan);

        return $this->success(new RadiologyScanResource($scan), 'Radiology scan deactivated.');
    }
}
