<?php

namespace App\Modules\Health\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Health\Application\Services\HealthRecordService;
use App\Modules\Health\Http\Requests\SurgeryRequest;
use App\Modules\Health\Http\Resources\SurgeryResource;
use App\Modules\Health\Infrastructure\Models\PatientSurgery;
use Illuminate\Http\Request;

class SurgeryController extends ApiController
{
    public function __construct(private readonly HealthRecordService $records) {}

    public function index(Request $request)
    {
        return $this->success(SurgeryResource::collection(
            PatientSurgery::query()->where('patient_user_id', $request->user()->id)->orderByDesc('id')->get()
        ), 'Surgeries.');
    }

    public function store(SurgeryRequest $request)
    {
        $record = $this->records->create($request->user(), PatientSurgery::class, $request->validated(), 'health.surgery');

        return $this->success(new SurgeryResource($record), 'Surgery created.', 201);
    }

    public function update(SurgeryRequest $request, PatientSurgery $surgery)
    {
        $this->authorize('update', $surgery);
        $record = $this->records->update($request->user(), $surgery, $request->validated(), 'health.surgery');

        return $this->success(new SurgeryResource($record), 'Surgery updated.');
    }

    public function destroy(Request $request, PatientSurgery $surgery)
    {
        $this->authorize('delete', $surgery);
        $this->records->delete($request->user(), $surgery, 'health.surgery');

        return $this->success(null, 'Surgery deleted.');
    }
}
