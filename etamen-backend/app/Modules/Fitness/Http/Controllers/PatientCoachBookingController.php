<?php

namespace App\Modules\Fitness\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Fitness\Application\Services\CoachBookingService;
use App\Modules\Fitness\Http\Requests\CancelFitnessBookingRequest;
use App\Modules\Fitness\Http\Requests\CreateCoachBookingRequest;
use App\Modules\Fitness\Http\Resources\CoachBookingResource;
use App\Modules\Fitness\Infrastructure\Models\CoachBooking;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PatientCoachBookingController extends ApiController
{
    public function __construct(private readonly CoachBookingService $bookingService) {}

    public function index(Request $request)
    {
        $bookings = CoachBooking::query()
            ->where('patient_user_id', $request->user()->id)
            ->with(['coachProvider', 'sessionType', 'availabilitySlot', 'payment.paymentMethod'])
            ->orderByDesc('id')
            ->limit($this->perPage($request))
            ->get();

        return $this->success(CoachBookingResource::collection($bookings), 'Patient coach bookings.');
    }

    public function store(CreateCoachBookingRequest $request)
    {
        $booking = $this->bookingService->create($request->user(), $request->validated());

        return $this->success(new CoachBookingResource($booking), 'Coach booking created.', 201);
    }

    public function show(Request $request, CoachBooking $booking)
    {
        $this->assertOwnBooking($request, $booking);

        $booking->load(['coachProvider', 'sessionType', 'availabilitySlot', 'payment.paymentMethod']);

        return $this->success(new CoachBookingResource($booking), 'Coach booking details.');
    }

    public function cancel(CancelFitnessBookingRequest $request, CoachBooking $booking)
    {
        $this->assertOwnBooking($request, $booking);
        $booking = $this->bookingService->cancelByPatient($request->user(), $booking, $request->validated('reason'));

        return $this->success(new CoachBookingResource($booking), 'Coach booking cancelled.');
    }

    private function assertOwnBooking(Request $request, CoachBooking $booking): void
    {
        if ((int) $booking->patient_user_id !== (int) $request->user()->id) {
            throw ValidationException::withMessages([
                'booking' => ['You cannot access this coach booking.'],
            ]);
        }
    }
}
