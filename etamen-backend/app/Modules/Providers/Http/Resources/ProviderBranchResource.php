<?php

namespace App\Modules\Providers\Http\Resources;

use App\Modules\Locations\Http\Resources\AreaResource;
use App\Modules\Locations\Http\Resources\CityResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderBranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider_id' => $this->provider_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'phone' => $this->phone,
            'address_ar' => $this->address_ar,
            'address_en' => $this->address_en,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_main' => $this->is_main,
            'is_active' => $this->is_active,
            'city' => new CityResource($this->whenLoaded('city')),
            'area' => new AreaResource($this->whenLoaded('area')),
        ];
    }
}
