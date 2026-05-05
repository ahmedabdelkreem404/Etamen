<?php

namespace App\Modules\Health\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HealthGoalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_user_id' => $this->patient_user_id,
            'goal_type' => $this->goal_type->value,
            'title' => $this->title,
            'target_value' => $this->target_value,
            'unit' => $this->unit,
            'target_date' => $this->target_date?->toDateString(),
            'notes' => $this->notes,
            'status' => $this->status->value,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
