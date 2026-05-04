<?php

namespace App\Modules\Patients\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Patients\Http\Requests\UpdatePatientProfileRequest;
use App\Modules\Patients\Http\Resources\PatientProfileResource;
use Illuminate\Http\Request;

class PatientProfileController extends ApiController
{
    public function show(Request $request)
    {
        return $this->success(
            new PatientProfileResource($request->user()->patientProfile),
            'Patient profile.',
        );
    }

    public function update(UpdatePatientProfileRequest $request)
    {
        $profile = $request->user()->patientProfile()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $request->validated(),
        );

        return $this->success(new PatientProfileResource($profile), 'Patient profile updated.');
    }
}
