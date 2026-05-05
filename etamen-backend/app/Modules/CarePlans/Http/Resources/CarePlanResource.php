<?php

namespace App\Modules\CarePlans\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarePlanResource extends JsonResource
{
    public const SAFETY_DISCLAIMER = 'هذه الخطة للتنظيم والمتابعة ولا تعتبر تشخيصًا أو علاجًا طبيًا. في حالة وجود مرض مزمن أو حمل أو أعراض خطيرة، يجب الرجوع للطبيب أو المختص.';

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_user_id' => $this->patient_user_id,
            'assigned_by_user_id' => $this->assigned_by_user_id,
            'provider_id' => $this->provider_id,
            'plan_type' => $this->plan_type->value,
            'title' => $this->title,
            'description' => $this->description,
            'goal_text' => $this->goal_text,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'status' => $this->status->value,
            'visibility' => $this->visibility->value,
            'source' => $this->source->value,
            'notes' => $this->notes,
            'metadata' => $this->metadata,
            'safety_disclaimer' => $this->safety_disclaimer ?: self::SAFETY_DISCLAIMER,
            'days' => CarePlanDayResource::collection($this->whenLoaded('days')),
            'foods' => CarePlanFoodItemResource::collection($this->whenLoaded('foodItems')),
            'instructions' => CarePlanInstructionResource::collection($this->whenLoaded('instructions')),
            'checkins_count' => $this->when(isset($this->checkins_count), $this->checkins_count),
            'meal_logs_count' => $this->when(isset($this->meal_logs_count), $this->meal_logs_count),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
