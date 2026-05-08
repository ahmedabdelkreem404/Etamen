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
            'whatsapp' => $this->whatsapp,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'district' => $this->district,
            'address_ar' => $this->address_ar,
            'address_en' => $this->address_en,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'working_hours' => $this->working_hours_json,
            'is_24_hours' => $this->is_24_hours,
            'home_service_radius_km' => $this->home_service_radius_km,
            'delivery_radius_km' => $this->delivery_radius_km,
            'is_main' => $this->is_main,
            'is_active' => $this->is_active,
            'city' => new CityResource($this->whenLoaded('city')),
            'area' => new AreaResource($this->whenLoaded('area')),
        ];
    }
}
