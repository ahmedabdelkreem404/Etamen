<?php

namespace App\Modules\Appointments\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'appointment_id' => $this->appointment_id,
            'patient_user_id' => $this->patient_user_id,
            'doctor_profile_id' => $this->doctor_profile_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'is_visible' => $this->is_visible,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
