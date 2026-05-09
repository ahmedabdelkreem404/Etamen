<?php

namespace App\Modules\Fitness\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoachSessionTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider_id' => $this->provider_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'duration_minutes' => $this->duration_minutes,
            'price' => $this->price,
            'session_mode' => $this->session_mode->value,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ];
    }
}
