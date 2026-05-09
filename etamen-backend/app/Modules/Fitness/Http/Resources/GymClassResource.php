<?php

namespace App\Modules\Fitness\Http\Resources;

use App\Modules\Providers\Http\Resources\ProviderBranchResource;
use App\Modules\Providers\Http\Resources\ProviderResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GymClassResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider_id' => $this->provider_id,
            'branch_id' => $this->branch_id,
            'coach_provider_id' => $this->coach_provider_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'capacity' => $this->capacity,
            'price' => $this->price,
            'is_active' => $this->is_active,
            'branch' => new ProviderBranchResource($this->whenLoaded('branch')),
            'coach' => new ProviderResource($this->whenLoaded('coachProvider')),
        ];
    }
}
