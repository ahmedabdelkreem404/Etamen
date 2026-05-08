<?php

namespace App\Modules\Radiology\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Radiology\Application\Services\RadiologyScanService;
use App\Modules\Radiology\Http\Requests\AdminRadiologyScanRequest;
use App\Modules\Radiology\Http\Resources\RadiologyScanResource;
use App\Modules\Radiology\Infrastructure\Models\RadiologyScan;
use Illuminate\Http\Request;

class AdminRadiologyScanController extends ApiController
{
    public function __construct(private readonly RadiologyScanService $scanService) {}

    public function index(Request $request)
    {
        $scans = RadiologyScan::query()
            ->with(['provider', 'branch', 'category'])
            ->when($request->integer('provider_id'), fn ($query, $providerId) => $query->where('provider_id', $providerId))
            ->when($request->integer('category_id'), fn ($query, $categoryId) => $query->where('radiology_scan_category_id', $categoryId))
            ->when($request->has('is_active'), fn ($query) => $query->where('is_active', $request->boolean('is_active')))
            ->orderByDesc('id')
            ->get();

        return $this->success(RadiologyScanResource::collection($scans), 'Admin radiology scans.');
    }

    public function store(AdminRadiologyScanRequest $request)
    {
        $data = $request->validated();
        $provider = Provider::query()->findOrFail($data['provider_id']);
        unset($data['provider_id']);

        $scan = $this->scanService->createForRadiologyProvider($request->user(), $provider, $data);

        return $this->success(new RadiologyScanResource($scan), 'Radiology scan created.', 201);
    }

    public function update(AdminRadiologyScanRequest $request, RadiologyScan $scan)
    {
        $data = $request->validated();

        if (isset($data['provider_id']) && (int) $data['provider_id'] !== (int) $scan->provider_id) {
            $scan->provider_id = $data['provider_id'];
            unset($data['provider_id']);
        }

        $scan = $this->scanService->update($request->user(), $scan, $data);

        return $this->success(new RadiologyScanResource($scan), 'Radiology scan updated.');
    }

    public function destroy(Request $request, RadiologyScan $scan)
    {
        $scan = $this->scanService->deactivate($request->user(), $scan);

        return $this->success(new RadiologyScanResource($scan), 'Radiology scan deactivated.');
    }
}
