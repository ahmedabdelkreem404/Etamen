<?php

namespace App\Modules\Fitness\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Fitness\Application\Services\GymBookingService;
use App\Modules\Fitness\Http\Requests\CancelFitnessBookingRequest;
use App\Modules\Fitness\Http\Requests\CreateGymBookingRequest;
use App\Modules\Fitness\Http\Resources\GymBookingResource;
use App\Modules\Fitness\Infrastructure\Models\GymBooking;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PatientGymBookingController extends ApiController
{
    public function __construct(private readonly GymBookingService $bookingService) {}

    public function index(Request $request)
    {
        $bookings = GymBooking::query()
            ->where('patient_user_id', $request->user()->id)
            ->with(['provider', 'branch', 'membershipPlan', 'gymClass', 'payment.paymentMethod'])
            ->orderByDesc('id')
            ->limit($this->perPage($request))
            ->get();

        return $this->success(GymBookingResource::collection($bookings), 'Patient gym bookings.');
    }

    public function store(CreateGymBookingRequest $request)
    {
        $booking = $this->bookingService->create($request->user(), $request->validated());

        return $this->success(new GymBookingResource($booking), 'Gym booking created.', 201);
    }

    public function show(Request $request, GymBooking $booking)
    {
        $this->assertOwnBooking($request, $booking);

        $booking->load(['provider', 'branch', 'membershipPlan', 'gymClass', 'payment.paymentMethod']);

        return $this->success(new GymBookingResource($booking), 'Gym booking details.');
    }

    public function cancel(CancelFitnessBookingRequest $request, GymBooking $booking)
    {
        $this->assertOwnBooking($request, $booking);
        $booking = $this->bookingService->cancelByPatient($request->user(), $booking, $request->validated('reason'));

        return $this->success(new GymBookingResource($booking), 'Gym booking cancelled.');
    }

    private function assertOwnBooking(Request $request, GymBooking $booking): void
    {
        if ((int) $booking->patient_user_id !== (int) $request->user()->id) {
            throw ValidationException::withMessages([
                'booking' => ['You cannot access this gym booking.'],
            ]);
        }
    }
}
