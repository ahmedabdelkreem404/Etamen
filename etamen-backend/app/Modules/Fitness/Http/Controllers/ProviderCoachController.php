<?php

namespace App\Modules\Fitness\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Fitness\Application\Services\FitnessAccessService;
use App\Modules\Fitness\Http\Requests\CoachAvailabilitySlotRequest;
use App\Modules\Fitness\Http\Requests\CoachPackageRequest;
use App\Modules\Fitness\Http\Requests\CoachSessionTypeRequest;
use App\Modules\Fitness\Http\Resources\CoachAvailabilitySlotResource;
use App\Modules\Fitness\Http\Resources\CoachBookingResource;
use App\Modules\Fitness\Http\Resources\CoachPackageResource;
use App\Modules\Fitness\Http\Resources\CoachSessionTypeResource;
use App\Modules\Fitness\Infrastructure\Models\CoachAvailabilitySlot;
use App\Modules\Fitness\Infrastructure\Models\CoachBooking;
use App\Modules\Fitness\Infrastructure\Models\CoachPackage;
use App\Modules\Fitness\Infrastructure\Models\CoachSessionType;
use Illuminate\Http\Request;

class ProviderCoachController extends ApiController
{
    public function __construct(private readonly FitnessAccessService $accessService) {}

    public function sessionTypes(Request $request)
    {
        $provider = $this->accessService->currentCoachFor($request->user());

        $sessionTypes = CoachSessionType::query()
            ->where('provider_id', $provider->id)
            ->orderBy('sort_order')
            ->limit($this->perPage($request, 100))
            ->get();

        return $this->success(CoachSessionTypeResource::collection($sessionTypes), 'Provider coach session types.');
    }

    public function storeSessionType(CoachSessionTypeRequest $request)
    {
        $provider = $this->accessService->currentCoachFor($request->user());
        $sessionType = CoachSessionType::query()->create($request->validated() + ['provider_id' => $provider->id]);

        return $this->success(new CoachSessionTypeResource($sessionType), 'Coach session type created.', 201);
    }

    public function updateSessionType(CoachSessionTypeRequest $request, CoachSessionType $sessionType)
    {
        $provider = $this->accessService->currentCoachFor($request->user());
        abort_if((int) $sessionType->provider_id !== (int) $provider->id, 403);

        $sessionType->update($request->validated() + ['provider_id' => $provider->id]);

        return $this->success(new CoachSessionTypeResource($sessionType->refresh()), 'Coach session type updated.');
    }

    public function destroySessionType(Request $request, CoachSessionType $sessionType)
    {
        $provider = $this->accessService->currentCoachFor($request->user());
        abort_if((int) $sessionType->provider_id !== (int) $provider->id, 403);

        $sessionType->update(['is_active' => false]);

        return $this->success(new CoachSessionTypeResource($sessionType->refresh()), 'Coach session type deactivated.');
    }

    public function availability(Request $request)
    {
        $provider = $this->accessService->currentCoachFor($request->user());

        $slots = CoachAvailabilitySlot::query()
            ->where('provider_id', $provider->id)
            ->orderBy('starts_at')
            ->limit($this->perPage($request, 100))
            ->get();

        return $this->success(CoachAvailabilitySlotResource::collection($slots), 'Provider coach availability.');
    }

    public function storeAvailability(CoachAvailabilitySlotRequest $request)
    {
        $provider = $this->accessService->currentCoachFor($request->user());
        $slot = CoachAvailabilitySlot::query()->create($request->validated() + ['provider_id' => $provider->id]);

        return $this->success(new CoachAvailabilitySlotResource($slot), 'Coach availability created.', 201);
    }

    public function updateAvailability(CoachAvailabilitySlotRequest $request, CoachAvailabilitySlot $slot)
    {
        $provider = $this->accessService->currentCoachFor($request->user());
        abort_if((int) $slot->provider_id !== (int) $provider->id, 403);

        $slot->update($request->validated() + ['provider_id' => $provider->id]);

        return $this->success(new CoachAvailabilitySlotResource($slot->refresh()), 'Coach availability updated.');
    }

    public function packages(Request $request)
    {
        $provider = $this->accessService->currentCoachFor($request->user());

        $packages = CoachPackage::query()
            ->where('provider_id', $provider->id)
            ->orderBy('price')
            ->limit($this->perPage($request, 100))
            ->get();

        return $this->success(CoachPackageResource::collection($packages), 'Provider coach packages.');
    }

    public function storePackage(CoachPackageRequest $request)
    {
        $provider = $this->accessService->currentCoachFor($request->user());
        $package = CoachPackage::query()->create($request->validated() + ['provider_id' => $provider->id]);

        return $this->success(new CoachPackageResource($package), 'Coach package created.', 201);
    }

    public function updatePackage(CoachPackageRequest $request, CoachPackage $package)
    {
        $provider = $this->accessService->currentCoachFor($request->user());
        abort_if((int) $package->provider_id !== (int) $provider->id, 403);

        $package->update($request->validated() + ['provider_id' => $provider->id]);

        return $this->success(new CoachPackageResource($package->refresh()), 'Coach package updated.');
    }

    public function destroyPackage(Request $request, CoachPackage $package)
    {
        $provider = $this->accessService->currentCoachFor($request->user());
        abort_if((int) $package->provider_id !== (int) $provider->id, 403);

        $package->update(['is_active' => false]);

        return $this->success(new CoachPackageResource($package->refresh()), 'Coach package deactivated.');
    }

    public function bookings(Request $request)
    {
        $provider = $this->accessService->currentCoachFor($request->user());

        $bookings = CoachBooking::query()
            ->where('coach_provider_id', $provider->id)
            ->with(['coachProvider', 'sessionType', 'availabilitySlot', 'payment.paymentMethod'])
            ->orderByDesc('id')
            ->limit($this->perPage($request))
            ->get();

        return $this->success(CoachBookingResource::collection($bookings), 'Provider coach bookings.');
    }
}
