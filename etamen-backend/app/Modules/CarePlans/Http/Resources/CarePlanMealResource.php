<?php

namespace App\Modules\CarePlans\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarePlanMealResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'care_plan_day_id' => $this->care_plan_day_id,
            'meal_type' => $this->meal_type->value,
            'title' => $this->title,
            'description' => $this->description,
            'calories' => $this->calories,
            'protein_g' => $this->protein_g,
            'carbs_g' => $this->carbs_g,
            'fat_g' => $this->fat_g,
            'instructions' => $this->instructions,
            'sort_order' => $this->sort_order,
            'is_required' => $this->is_required,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
