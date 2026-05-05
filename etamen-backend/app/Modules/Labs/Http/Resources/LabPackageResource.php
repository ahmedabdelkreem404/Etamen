<?php

namespace App\Modules\Labs\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabPackageResource extends JsonResource
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
            'price' => $this->price,
            'is_active' => $this->is_active,
            'tests' => LabTestResource::collection($this->whenLoaded('tests')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
