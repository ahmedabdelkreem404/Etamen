<?php

namespace App\Modules\CarePlans\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MealLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'care_plan_id' => $this->care_plan_id,
            'care_plan_meal_id' => $this->care_plan_meal_id,
            'patient_user_id' => $this->patient_user_id,
            'logged_at' => $this->logged_at?->toISOString(),
            'meal_type' => $this->meal_type?->value,
            'status' => $this->status->value,
            'description' => $this->description,
            'photo' => new UploadedMealPhotoResource($this->whenLoaded('photo')),
            'notes' => $this->notes,
            'metadata' => $this->metadata,
            'safe_disclaimer' => CarePlanResource::SAFETY_DISCLAIMER,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
