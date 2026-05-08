<?php

namespace App\Modules\Radiology\Http\Resources;

use App\Modules\Providers\Http\Resources\ProviderBranchResource;
use App\Modules\Providers\Http\Resources\ProviderResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RadiologyScanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider_id' => $this->provider_id,
            'branch_id' => $this->branch_id,
            'radiology_scan_category_id' => $this->radiology_scan_category_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'preparation_ar' => $this->preparation_ar,
            'preparation_en' => $this->preparation_en,
            'duration_minutes' => $this->duration_minutes,
            'base_price' => $this->base_price,
            'requires_preparation' => $this->requires_preparation,
            'requires_fasting' => $this->requires_fasting,
            'contrast_required' => $this->contrast_required,
            'home_available' => $this->home_available,
            'branch_available' => $this->branch_available,
            'report_delivery_enabled' => $this->report_delivery_enabled,
            'is_active' => $this->is_active,
            'category' => new RadiologyScanCategoryResource($this->whenLoaded('category')),
            'provider' => new ProviderResource($this->whenLoaded('provider')),
            'branch' => new ProviderBranchResource($this->whenLoaded('branch')),
            'instructions' => RadiologyPreparationInstructionResource::collection($this->whenLoaded('instructions')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
