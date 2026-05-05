<?php

namespace App\Modules\CarePlans\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarePlanCheckinResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'care_plan_id' => $this->care_plan_id,
            'patient_user_id' => $this->patient_user_id,
            'checkin_date' => $this->checkin_date?->toDateString(),
            'commitment_score' => $this->commitment_score,
            'energy_level' => $this->energy_level,
            'hunger_level' => $this->hunger_level,
            'sleep_quality' => $this->sleep_quality,
            'mood' => $this->mood?->value,
            'symptoms_notes' => $this->symptoms_notes,
            'general_notes' => $this->general_notes,
            'safe_disclaimer' => CarePlanResource::SAFETY_DISCLAIMER,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
