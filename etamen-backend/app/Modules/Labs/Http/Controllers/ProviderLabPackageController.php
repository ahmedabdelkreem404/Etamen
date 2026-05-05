<?php

namespace App\Modules\Labs\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Labs\Application\Services\LabAccessService;
use App\Modules\Labs\Application\Services\LabPackageService;
use App\Modules\Labs\Http\Requests\LabPackageRequest;
use App\Modules\Labs\Http\Resources\LabPackageResource;
use App\Modules\Labs\Infrastructure\Models\LabPackage;
use Illuminate\Http\Request;

class ProviderLabPackageController extends ApiController
{
    public function __construct(
        private readonly LabAccessService $accessService,
        private readonly LabPackageService $packageService,
    ) {}

    public function index(Request $request)
    {
        $lab = $this->accessService->currentLabFor($request->user());
        $packages = LabPackage::query()
            ->where('provider_id', $lab->id)
            ->with('tests')
            ->orderByDesc('id')
            ->get();

        return $this->success(LabPackageResource::collection($packages), 'Provider lab packages.');
    }

    public function store(LabPackageRequest $request)
    {
        $package = $this->packageService->create($request->user(), $request->validated());

        return $this->success(new LabPackageResource($package), 'Lab package created.', 201);
    }

    public function show(LabPackage $package)
    {
        $this->authorize('view', $package);

        return $this->success(new LabPackageResource($package->load('tests')), 'Lab package details.');
    }

    public function update(LabPackageRequest $request, LabPackage $package)
    {
        $this->authorize('update', $package);
        $package = $this->packageService->update($request->user(), $package, $request->validated());

        return $this->success(new LabPackageResource($package), 'Lab package updated.');
    }

    public function destroy(Request $request, LabPackage $package)
    {
        $this->authorize('delete', $package);
        $package = $this->packageService->deactivate($request->user(), $package);

        return $this->success(new LabPackageResource($package), 'Lab package deactivated.');
    }
}
