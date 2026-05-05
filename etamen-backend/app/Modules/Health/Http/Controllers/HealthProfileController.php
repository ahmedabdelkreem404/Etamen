<?php

namespace App\Modules\Health\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Health\Application\Services\HealthProfileService;
use App\Modules\Health\Http\Requests\HealthProfileRequest;
use App\Modules\Health\Http\Resources\HealthProfileResource;
use Illuminate\Http\Request;

class HealthProfileController extends ApiController
{
    public function __construct(private readonly HealthProfileService $profileService) {}

    public function show(Request $request)
    {
        return $this->success(new HealthProfileResource($this->profileService->profileFor($request->user())), 'Health profile.');
    }

    public function update(HealthProfileRequest $request)
    {
        $profile = $this->profileService->update($request->user(), $request->validated());

        return $this->success(new HealthProfileResource($profile), 'Health profile updated.');
    }
}
