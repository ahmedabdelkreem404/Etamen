<?php

namespace App\Modules\Fitness\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Fitness\Application\Services\FitnessAccessService;
use App\Modules\Fitness\Http\Requests\GymClassRequest;
use App\Modules\Fitness\Http\Requests\GymMembershipPlanRequest;
use App\Modules\Fitness\Http\Resources\GymBookingResource;
use App\Modules\Fitness\Http\Resources\GymClassResource;
use App\Modules\Fitness\Http\Resources\GymMembershipPlanResource;
use App\Modules\Fitness\Infrastructure\Models\GymBooking;
use App\Modules\Fitness\Infrastructure\Models\GymClassModel;
use App\Modules\Fitness\Infrastructure\Models\GymMembershipPlan;
use Illuminate\Http\Request;

class ProviderGymController extends ApiController
{
    public function __construct(private readonly FitnessAccessService $accessService) {}

    public function plans(Request $request)
    {
        $provider = $this->accessService->currentGymFor($request->user());

        $plans = GymMembershipPlan::query()
            ->where('provider_id', $provider->id)
            ->with('branch')
            ->orderBy('sort_order')
            ->limit($this->perPage($request, 100))
            ->get();

        return $this->success(GymMembershipPlanResource::collection($plans), 'Provider gym plans.');
    }

    public function storePlan(GymMembershipPlanRequest $request)
    {
        $provider = $this->accessService->currentGymFor($request->user());
        $plan = GymMembershipPlan::query()->create($request->validated() + ['provider_id' => $provider->id]);

        return $this->success(new GymMembershipPlanResource($plan->load('branch')), 'Gym plan created.', 201);
    }

    public function updatePlan(GymMembershipPlanRequest $request, GymMembershipPlan $plan)
    {
        $provider = $this->accessService->currentGymFor($request->user());
        abort_if((int) $plan->provider_id !== (int) $provider->id, 403);

        $plan->update($request->validated() + ['provider_id' => $provider->id]);

        return $this->success(new GymMembershipPlanResource($plan->refresh()->load('branch')), 'Gym plan updated.');
    }

    public function destroyPlan(Request $request, GymMembershipPlan $plan)
    {
        $provider = $this->accessService->currentGymFor($request->user());
        abort_if((int) $plan->provider_id !== (int) $provider->id, 403);

        $plan->update(['is_active' => false]);

        return $this->success(new GymMembershipPlanResource($plan->refresh()), 'Gym plan deactivated.');
    }

    public function classes(Request $request)
    {
        $provider = $this->accessService->currentGymFor($request->user());

        $classes = GymClassModel::query()
            ->where('provider_id', $provider->id)
            ->with(['branch', 'coachProvider'])
            ->orderBy('starts_at')
            ->limit($this->perPage($request, 100))
            ->get();

        return $this->success(GymClassResource::collection($classes), 'Provider gym classes.');
    }

    public function storeClass(GymClassRequest $request)
    {
        $provider = $this->accessService->currentGymFor($request->user());
        $class = GymClassModel::query()->create($request->validated() + ['provider_id' => $provider->id]);

        return $this->success(new GymClassResource($class->load(['branch', 'coachProvider'])), 'Gym class created.', 201);
    }

    public function updateClass(GymClassRequest $request, GymClassModel $class)
    {
        $provider = $this->accessService->currentGymFor($request->user());
        abort_if((int) $class->provider_id !== (int) $provider->id, 403);

        $class->update($request->validated() + ['provider_id' => $provider->id]);

        return $this->success(new GymClassResource($class->refresh()->load(['branch', 'coachProvider'])), 'Gym class updated.');
    }

    public function destroyClass(Request $request, GymClassModel $class)
    {
        $provider = $this->accessService->currentGymFor($request->user());
        abort_if((int) $class->provider_id !== (int) $provider->id, 403);

        $class->update(['is_active' => false]);

        return $this->success(new GymClassResource($class->refresh()), 'Gym class deactivated.');
    }

    public function bookings(Request $request)
    {
        $provider = $this->accessService->currentGymFor($request->user());

        $bookings = GymBooking::query()
            ->where('provider_id', $provider->id)
            ->with(['provider', 'branch', 'membershipPlan', 'gymClass', 'payment.paymentMethod'])
            ->orderByDesc('id')
            ->limit($this->perPage($request))
            ->get();

        return $this->success(GymBookingResource::collection($bookings), 'Provider gym bookings.');
    }
}
