<?php

namespace App\Modules\Labs\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabTestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'catalog_type' => 'test',
            'provider_id' => $this->provider_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'code' => $this->code,
            'price' => $this->price,
            'sample_type' => $this->sample_type,
            'preparation_instructions_ar' => $this->preparation_instructions_ar,
            'preparation_instructions_en' => $this->preparation_instructions_en,
            'result_time_hours' => $this->result_time_hours,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
