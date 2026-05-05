<?php

namespace App\Modules\Health\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Health\Application\Services\HealthRecordService;
use App\Modules\Health\Http\Requests\AllergyRequest;
use App\Modules\Health\Http\Resources\AllergyResource;
use App\Modules\Health\Infrastructure\Models\PatientAllergy;
use Illuminate\Http\Request;

class AllergyController extends ApiController
{
    public function __construct(private readonly HealthRecordService $records) {}

    public function index(Request $request)
    {
        return $this->success(AllergyResource::collection(
            PatientAllergy::query()->where('patient_user_id', $request->user()->id)->orderByDesc('id')->get()
        ), 'Allergies.');
    }

    public function store(AllergyRequest $request)
    {
        $record = $this->records->create($request->user(), PatientAllergy::class, $request->validated(), 'health.allergy');

        return $this->success(new AllergyResource($record), 'Allergy created.', 201);
    }

    public function update(AllergyRequest $request, PatientAllergy $allergy)
    {
        $this->authorize('update', $allergy);
        $record = $this->records->update($request->user(), $allergy, $request->validated(), 'health.allergy');

        return $this->success(new AllergyResource($record), 'Allergy updated.');
    }

    public function destroy(Request $request, PatientAllergy $allergy)
    {
        $this->authorize('delete', $allergy);
        $this->records->delete($request->user(), $allergy, 'health.allergy');

        return $this->success(null, 'Allergy deleted.');
    }
}
