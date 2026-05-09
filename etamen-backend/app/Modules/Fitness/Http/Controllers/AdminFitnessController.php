<?php

namespace App\Modules\Fitness\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Fitness\Http\Resources\CoachBookingResource;
use App\Modules\Fitness\Http\Resources\GymBookingResource;
use App\Modules\Fitness\Infrastructure\Models\CoachBooking;
use App\Modules\Fitness\Infrastructure\Models\GymBooking;
use Illuminate\Http\Request;

class AdminFitnessController extends ApiController
{
    public function gymBookings(Request $request)
    {
        $bookings = GymBooking::query()
            ->with(['provider', 'branch', 'membershipPlan', 'gymClass', 'payment.paymentMethod'])
            ->when($request->integer('provider_id'), fn ($query, $providerId) => $query->where('provider_id', $providerId))
            ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('id')
            ->limit($this->perPage($request))
            ->get();

        return $this->success(GymBookingResource::collection($bookings), 'Admin gym bookings.');
    }

    public function gymBooking(GymBooking $booking)
    {
        $booking->load(['provider', 'branch', 'membershipPlan', 'gymClass', 'payment.paymentMethod']);

        return $this->success(new GymBookingResource($booking), 'Admin gym booking details.');
    }

    public function coachBookings(Request $request)
    {
        $bookings = CoachBooking::query()
            ->with(['coachProvider', 'sessionType', 'availabilitySlot', 'payment.paymentMethod'])
            ->when($request->integer('coach_provider_id'), fn ($query, $providerId) => $query->where('coach_provider_id', $providerId))
            ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('id')
            ->limit($this->perPage($request))
            ->get();

        return $this->success(CoachBookingResource::collection($bookings), 'Admin coach bookings.');
    }

    public function coachBooking(CoachBooking $booking)
    {
        $booking->load(['coachProvider', 'sessionType', 'availabilitySlot', 'payment.paymentMethod']);

        return $this->success(new CoachBookingResource($booking), 'Admin coach booking details.');
    }
}
