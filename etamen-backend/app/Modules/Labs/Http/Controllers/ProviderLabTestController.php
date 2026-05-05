<?php

namespace App\Modules\Labs\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Labs\Application\Services\LabAccessService;
use App\Modules\Labs\Application\Services\LabTestService;
use App\Modules\Labs\Http\Requests\LabTestRequest;
use App\Modules\Labs\Http\Resources\LabTestResource;
use App\Modules\Labs\Infrastructure\Models\LabTest;
use Illuminate\Http\Request;

class ProviderLabTestController extends ApiController
{
    public function __construct(
        private readonly LabAccessService $accessService,
        private readonly LabTestService $testService,
    ) {}

    public function index(Request $request)
    {
        $lab = $this->accessService->currentLabFor($request->user());
        $tests = LabTest::query()
            ->where('provider_id', $lab->id)
            ->orderByDesc('id')
            ->get();

        return $this->success(LabTestResource::collection($tests), 'Provider lab tests.');
    }

    public function store(LabTestRequest $request)
    {
        $test = $this->testService->create($request->user(), $request->validated());

        return $this->success(new LabTestResource($test), 'Lab test created.', 201);
    }

    public function show(LabTest $test)
    {
        $this->authorize('view', $test);

        return $this->success(new LabTestResource($test), 'Lab test details.');
    }

    public function update(LabTestRequest $request, LabTest $test)
    {
        $this->authorize('update', $test);
        $test = $this->testService->update($request->user(), $test, $request->validated());

        return $this->success(new LabTestResource($test), 'Lab test updated.');
    }

    public function destroy(Request $request, LabTest $test)
    {
        $this->authorize('delete', $test);
        $test = $this->testService->deactivate($request->user(), $test);

        return $this->success(new LabTestResource($test), 'Lab test deactivated.');
    }
}
