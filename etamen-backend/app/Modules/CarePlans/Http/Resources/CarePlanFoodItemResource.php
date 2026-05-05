<?php

namespace App\Modules\CarePlans\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarePlanFoodItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'care_plan_id' => $this->care_plan_id,
            'category' => $this->category->value,
            'name' => $this->name,
            'notes' => $this->notes,
            'safety_note' => 'هذه الأطعمة مرتبطة بالخطة فقط وليست منعًا طبيًا عامًا.',
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
