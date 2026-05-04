<?php

namespace App\Modules\Appointments\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentSlotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'doctor_profile_id' => $this->doctor_profile_id,
            'provider_id' => $this->provider_id,
            'branch_id' => $this->branch_id,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'status' => $this->status->value,
            'generated_from_schedule_id' => $this->generated_from_schedule_id,
        ];
    }
}
