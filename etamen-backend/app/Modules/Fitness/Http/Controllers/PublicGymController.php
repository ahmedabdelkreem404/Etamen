<?php

namespace App\Modules\Fitness\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Fitness\Http\Resources\GymClassResource;
use App\Modules\Fitness\Http\Resources\GymMembershipPlanResource;
use App\Modules\Fitness\Http\Resources\GymResource;
use App\Modules\Fitness\Infrastructure\Models\GymClassModel;
use App\Modules\Fitness\Infrastructure\Models\GymMembershipPlan;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PublicGymController extends ApiController
{
    public function index(Request $request)
    {
        $gyms = Provider::query()
            ->publiclyVisible()
            ->where('type', ProviderType::Gym)
            ->with(['gymProfile', 'branches.city', 'branches.area'])
            ->withCount(['gymMembershipPlans', 'gymClasses'])
            ->orderBy('name_ar')
            ->limit($this->perPage($request))
            ->get();

        return $this->success(GymResource::collection($gyms), 'Gyms.');
    }

    public function show(Provider $gym)
    {
        $this->assertPublicGym($gym);

        $gym->load(['gymProfile', 'branches.city', 'branches.area'])
            ->loadCount(['gymMembershipPlans', 'gymClasses']);

        return $this->success(new GymResource($gym), 'Gym details.');
    }

    public function membershipPlans(Request $request, Provider $gym)
    {
        $this->assertPublicGym($gym);

        $plans = GymMembershipPlan::query()
            ->publiclyVisible()
            ->where('provider_id', $gym->id)
            ->with('branch.city', 'branch.area')
            ->orderBy('sort_order')
            ->orderBy('name_ar')
            ->limit($this->perPage($request, 50))
            ->get();

        return $this->success(GymMembershipPlanResource::collection($plans), 'Gym membership plans.');
    }

    public function classes(Request $request, Provider $gym)
    {
        $this->assertPublicGym($gym);

        $classes = GymClassModel::query()
            ->publiclyVisible()
            ->where('provider_id', $gym->id)
            ->with(['branch.city', 'branch.area', 'coachProvider.coachProfile'])
            ->orderBy('starts_at')
            ->limit($this->perPage($request, 50))
            ->get();

        return $this->success(GymClassResource::collection($classes), 'Gym classes.');
    }

    private function assertPublicGym(Provider $gym): void
    {
        if ($gym->type !== ProviderType::Gym || $gym->status !== ProviderStatus::Approved || ! $gym->is_active) {
            throw ValidationException::withMessages([
                'gym' => ['The selected gym is not available.'],
            ]);
        }
    }
}
