<?php

namespace App\Modules\Health\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Health\Application\Services\HealthRecordService;
use App\Modules\Health\Http\Requests\ChronicDiseaseRequest;
use App\Modules\Health\Http\Resources\ChronicDiseaseResource;
use App\Modules\Health\Infrastructure\Models\PatientChronicDisease;
use Illuminate\Http\Request;

class ChronicDiseaseController extends ApiController
{
    public function __construct(private readonly HealthRecordService $records) {}

    public function index(Request $request)
    {
        return $this->success(ChronicDiseaseResource::collection(
            PatientChronicDisease::query()->where('patient_user_id', $request->user()->id)->orderByDesc('id')->get()
        ), 'Chronic diseases.');
    }

    public function store(ChronicDiseaseRequest $request)
    {
        $record = $this->records->create($request->user(), PatientChronicDisease::class, $request->validated(), 'health.chronic_disease');

        return $this->success(new ChronicDiseaseResource($record), 'Chronic disease created.', 201);
    }

    public function update(ChronicDiseaseRequest $request, PatientChronicDisease $disease)
    {
        $this->authorize('update', $disease);
        $record = $this->records->update($request->user(), $disease, $request->validated(), 'health.chronic_disease');

        return $this->success(new ChronicDiseaseResource($record), 'Chronic disease updated.');
    }

    public function destroy(Request $request, PatientChronicDisease $disease)
    {
        $this->authorize('delete', $disease);
        $this->records->delete($request->user(), $disease, 'health.chronic_disease');

        return $this->success(null, 'Chronic disease deleted.');
    }
}
