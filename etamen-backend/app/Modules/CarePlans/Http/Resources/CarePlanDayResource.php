<?php

namespace App\Modules\CarePlans\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarePlanDayResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'care_plan_id' => $this->care_plan_id,
            'day_number' => $this->day_number,
            'day_date' => $this->day_date?->toDateString(),
            'title' => $this->title,
            'instructions' => $this->instructions,
            'is_active' => $this->is_active,
            'meals' => CarePlanMealResource::collection($this->whenLoaded('meals')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
