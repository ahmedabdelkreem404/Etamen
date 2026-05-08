<?php

namespace App\Modules\Providers\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Identity\Http\Resources\UserResource;
use App\Modules\Providers\Application\Services\ProviderRegistrationService;
use App\Modules\Providers\Http\Requests\RegisterDoctorRequest;
use App\Modules\Providers\Http\Requests\RegisterLabRequest;
use App\Modules\Providers\Http\Requests\RegisterPharmacyRequest;
use App\Modules\Providers\Http\Requests\RegisterProviderRequest;
use App\Modules\Providers\Http\Resources\ProviderResource;

class ProviderRegistrationController extends ApiController
{
    public function __construct(private readonly ProviderRegistrationService $registrationService) {}

    public function provider(RegisterProviderRequest $request)
    {
        $result = $this->registrationService->registerGeneric($request->validated());

        return $this->success([
            'user' => new UserResource($result['user']),
            'provider' => new ProviderResource($result['provider']->load([
                'doctorProfile.specialties',
                'pharmacyProfile',
                'labProfile',
                'hospitalProfile',
                'clinicProfile',
                'medicalCenterProfile',
                'radiologyProfile',
                'gymProfile',
                'coachProfile',
                'physiotherapyProfile',
                'homeHealthcareProfile',
                'branches',
                'approvalRequests',
            ])),
            'token' => $result['token'],
            'token_type' => $result['token_type'],
        ], 'Provider registered for review.', 201);
    }

    public function doctor(RegisterDoctorRequest $request)
    {
        $result = $this->registrationService->registerDoctor($request->validated());

        return $this->success([
            'user' => new UserResource($result['user']),
            'provider' => new ProviderResource($result['provider']->load(['doctorProfile.specialties', 'branches', 'approvalRequests'])),
            'token' => $result['token'],
            'token_type' => $result['token_type'],
        ], 'Doctor provider registered for review.', 201);
    }

    public function pharmacy(RegisterPharmacyRequest $request)
    {
        $result = $this->registrationService->registerPharmacy($request->validated());

        return $this->success([
            'user' => new UserResource($result['user']),
            'provider' => new ProviderResource($result['provider']->load(['pharmacyProfile', 'branches', 'approvalRequests'])),
            'token' => $result['token'],
            'token_type' => $result['token_type'],
        ], 'Pharmacy provider registered for review.', 201);
    }

    public function lab(RegisterLabRequest $request)
    {
        $result = $this->registrationService->registerLab($request->validated());

        return $this->success([
            'user' => new UserResource($result['user']),
            'provider' => new ProviderResource($result['provider']->load(['labProfile', 'branches', 'approvalRequests'])),
            'token' => $result['token'],
            'token_type' => $result['token_type'],
        ], 'Lab provider registered for review.', 201);
    }
}
