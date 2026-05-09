<?php

namespace App\Modules\Providers\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HospitalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $mainBranch = $this->relationLoaded('branches')
            ? $this->branches->sortByDesc('is_main')->first()
            : null;
        $profile = $this->relationLoaded('hospitalProfile') ? $this->hospitalProfile : null;

        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'slug' => $this->slug,
            'description_ar' => $profile?->description_ar ?? $this->description_ar,
            'description_en' => $profile?->description_en ?? $this->description_en,
            'phone' => $this->phone,
            'verified' => true,
            'primary_branch_name' => $mainBranch?->name_ar ?? $mainBranch?->name_en,
            'primary_area_name' => $mainBranch?->area?->name_ar ?? $mainBranch?->area?->name_en,
            'primary_city_name' => $mainBranch?->city?->name_ar ?? $mainBranch?->city?->name_en,
            'primary_address' => $mainBranch?->address_ar
                ?? $mainBranch?->address_en
                ?? $mainBranch?->address_line_1,
            'latitude' => $mainBranch?->latitude,
            'longitude' => $mainBranch?->longitude,
            'departments_count' => (int) ($this->departments_count ?? 0),
            'doctors_count' => (int) ($this->doctors_count ?? 0),
            'emergency_available' => (bool) ($profile?->emergency_available ?? false),
            'has_outpatient' => (bool) ($profile?->has_outpatient ?? true),
            'has_inpatient' => (bool) ($profile?->has_inpatient ?? false),
            'has_icu' => (bool) ($profile?->has_icu ?? false),
            'has_ambulance' => (bool) ($profile?->has_ambulance ?? false),
            'branches' => ProviderBranchResource::collection($this->whenLoaded('branches')),
        ];
    }
}
