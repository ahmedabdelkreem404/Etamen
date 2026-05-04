<?php

namespace App\Modules\Providers\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Providers\Application\Services\SpecialtyService;
use App\Modules\Providers\Http\Requests\SpecialtyRequest;
use App\Modules\Providers\Http\Resources\SpecialtyResource;
use App\Modules\Providers\Infrastructure\Models\Specialty;
use Illuminate\Http\Request;

class AdminSpecialtyController extends ApiController
{
    public function __construct(private readonly SpecialtyService $specialtyService) {}

    public function store(SpecialtyRequest $request)
    {
        $this->authorize('create', Specialty::class);

        return $this->success(
            new SpecialtyResource($this->specialtyService->create($request->validated(), $request->user())),
            'Specialty created.',
            201,
        );
    }

    public function update(SpecialtyRequest $request, Specialty $specialty)
    {
        $this->authorize('update', $specialty);

        return $this->success(
            new SpecialtyResource($this->specialtyService->update($specialty, $request->validated(), $request->user())),
            'Specialty updated.',
        );
    }

    public function destroy(Request $request, Specialty $specialty)
    {
        $this->authorize('delete', $specialty);
        $this->specialtyService->delete($specialty, $request->user());

        return $this->success(null, 'Specialty deleted.');
    }
}
