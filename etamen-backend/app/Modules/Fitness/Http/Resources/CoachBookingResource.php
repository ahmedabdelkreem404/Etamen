<?php

namespace App\Modules\Fitness\Http\Resources;

use App\Modules\Payments\Http\Resources\PaymentStatusResource;
use App\Modules\Providers\Http\Resources\ProviderResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoachBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_number' => $this->booking_number,
            'patient_user_id' => $this->patient_user_id,
            'coach_provider_id' => $this->coach_provider_id,
            'session_type_id' => $this->session_type_id,
            'availability_slot_id' => $this->availability_slot_id,
            'status' => $this->status->value,
            'total_amount' => $this->total_amount,
            'payment_id' => $this->payment_id,
            'patient_goal' => $this->patient_goal,
            'coach_provider' => new ProviderResource($this->whenLoaded('coachProvider')),
            'session_type' => new CoachSessionTypeResource($this->whenLoaded('sessionType')),
            'availability_slot' => new CoachAvailabilitySlotResource($this->whenLoaded('availabilitySlot')),
            'payment' => new PaymentStatusResource($this->whenLoaded('payment')),
        ];
    }
}
