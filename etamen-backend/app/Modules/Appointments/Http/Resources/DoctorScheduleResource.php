<?php

namespace App\Modules\Appointments\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'doctor_profile_id' => $this->doctor_profile_id,
            'provider_id' => $this->provider_id,
            'branch_id' => $this->branch_id,
            'name' => $this->name,
            'is_active' => $this->is_active,
            'slot_duration_minutes' => $this->slot_duration_minutes,
            'buffer_minutes' => $this->buffer_minutes,
            'max_days_ahead' => $this->max_days_ahead,
            'days' => DoctorScheduleDayResource::collection($this->whenLoaded('days')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
