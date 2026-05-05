<?php

namespace App\Modules\Health\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Health\Application\Services\HealthRecordService;
use App\Modules\Health\Http\Requests\CurrentMedicationRequest;
use App\Modules\Health\Http\Resources\CurrentMedicationResource;
use App\Modules\Health\Infrastructure\Models\PatientCurrentMedication;
use Illuminate\Http\Request;

class CurrentMedicationController extends ApiController
{
    public function __construct(private readonly HealthRecordService $records) {}

    public function index(Request $request)
    {
        return $this->success(CurrentMedicationResource::collection(
            PatientCurrentMedication::query()->where('patient_user_id', $request->user()->id)->orderByDesc('id')->get()
        ), 'Current medications.');
    }

    public function store(CurrentMedicationRequest $request)
    {
        $record = $this->records->create($request->user(), PatientCurrentMedication::class, $request->validated(), 'health.current_medication');

        return $this->success(new CurrentMedicationResource($record), 'Current medication created.', 201);
    }

    public function update(CurrentMedicationRequest $request, PatientCurrentMedication $medication)
    {
        $this->authorize('update', $medication);
        $record = $this->records->update($request->user(), $medication, $request->validated(), 'health.current_medication');

        return $this->success(new CurrentMedicationResource($record), 'Current medication updated.');
    }

    public function destroy(Request $request, PatientCurrentMedication $medication)
    {
        $this->authorize('delete', $medication);
        $this->records->delete($request->user(), $medication, 'health.current_medication');

        return $this->success(null, 'Current medication deleted.');
    }
}
