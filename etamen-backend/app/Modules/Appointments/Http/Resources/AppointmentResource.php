<?php

namespace App\Modules\Appointments\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'appointment_number' => $this->appointment_number,
            'patient_user_id' => $this->patient_user_id,
            'doctor_profile_id' => $this->doctor_profile_id,
            'provider_id' => $this->provider_id,
            'branch_id' => $this->branch_id,
            'appointment_slot_id' => $this->appointment_slot_id,
            'consultation_type' => $this->consultation_type->value,
            'problem_description' => $this->problem_description,
            'price' => $this->price,
            'currency' => $this->currency,
            'status' => $this->status->value,
            'payment_id' => $this->payment_id,
            'booked_at' => $this->booked_at?->toISOString(),
            'confirmed_at' => $this->confirmed_at?->toISOString(),
            'accepted_at' => $this->accepted_at?->toISOString(),
            'rejected_at' => $this->rejected_at?->toISOString(),
            'cancelled_at' => $this->cancelled_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'no_show_at' => $this->no_show_at?->toISOString(),
            'slot' => new AppointmentSlotResource($this->whenLoaded('slot')),
            'status_histories' => AppointmentStatusHistoryResource::collection($this->whenLoaded('statusHistories')),
            'review' => new AppointmentReviewResource($this->whenLoaded('review')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
