<?php

namespace App\Modules\Providers\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HospitalDepartmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'hospital_provider_id' => $this->hospital_provider_id,
            'specialty_id' => $this->specialty_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'doctors_count' => (int) ($this->doctors_count ?? 0),
            'specialty' => new SpecialtyResource($this->whenLoaded('specialty')),
        ];
    }
}
