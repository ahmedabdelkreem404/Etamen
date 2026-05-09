<?php

namespace App\Modules\Fitness\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Fitness\Http\Resources\CoachAvailabilitySlotResource;
use App\Modules\Fitness\Http\Resources\CoachPackageResource;
use App\Modules\Fitness\Http\Resources\CoachResource;
use App\Modules\Fitness\Http\Resources\CoachSessionTypeResource;
use App\Modules\Fitness\Infrastructure\Models\CoachAvailabilitySlot;
use App\Modules\Fitness\Infrastructure\Models\CoachPackage;
use App\Modules\Fitness\Infrastructure\Models\CoachSessionType;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PublicCoachController extends ApiController
{
    public function index(Request $request)
    {
        $coaches = Provider::query()
            ->publiclyVisible()
            ->whereIn('type', [ProviderType::FitnessCoach, ProviderType::NutritionCoach])
            ->with('coachProfile')
            ->withCount(['coachSessionTypes', 'coachAvailabilitySlots', 'coachPackages'])
            ->when($request->string('type')->toString(), fn ($query, $type) => $query->where('type', $type))
            ->orderBy('name_ar')
            ->limit($this->perPage($request))
            ->get();

        return $this->success(CoachResource::collection($coaches), 'Coaches.');
    }

    public function show(Provider $coach)
    {
        $this->assertPublicCoach($coach);

        $coach->load('coachProfile')
            ->loadCount(['coachSessionTypes', 'coachAvailabilitySlots', 'coachPackages']);

        return $this->success(new CoachResource($coach), 'Coach details.');
    }

    public function sessionTypes(Request $request, Provider $coach)
    {
        $this->assertPublicCoach($coach);

        $sessionTypes = CoachSessionType::query()
            ->publiclyVisible()
            ->where('provider_id', $coach->id)
            ->orderBy('sort_order')
            ->orderBy('name_ar')
            ->limit($this->perPage($request, 50))
            ->get();

        return $this->success(CoachSessionTypeResource::collection($sessionTypes), 'Coach session types.');
    }

    public function availability(Request $request, Provider $coach)
    {
        $this->assertPublicCoach($coach);

        $slots = CoachAvailabilitySlot::query()
            ->publiclyVisible()
            ->where('provider_id', $coach->id)
            ->orderBy('starts_at')
            ->limit($this->perPage($request, 100))
            ->get();

        return $this->success(CoachAvailabilitySlotResource::collection($slots), 'Coach availability.');
    }

    public function packages(Request $request, Provider $coach)
    {
        $this->assertPublicCoach($coach);

        $packages = CoachPackage::query()
            ->publiclyVisible()
            ->where('provider_id', $coach->id)
            ->orderBy('price')
            ->limit($this->perPage($request, 50))
            ->get();

        return $this->success(CoachPackageResource::collection($packages), 'Coach packages.');
    }

    private function assertPublicCoach(Provider $coach): void
    {
        if (
            ! in_array($coach->type, [ProviderType::FitnessCoach, ProviderType::NutritionCoach], true)
            || $coach->status !== ProviderStatus::Approved
            || ! $coach->is_active
        ) {
            throw ValidationException::withMessages([
                'coach' => ['The selected coach is not available.'],
            ]);
        }
    }
}
