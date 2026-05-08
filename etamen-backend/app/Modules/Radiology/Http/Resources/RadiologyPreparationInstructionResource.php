<?php

namespace App\Modules\Radiology\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RadiologyPreparationInstructionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'radiology_scan_category_id' => $this->radiology_scan_category_id,
            'radiology_scan_id' => $this->radiology_scan_id,
            'title_ar' => $this->title_ar,
            'title_en' => $this->title_en,
            'body_ar' => $this->body_ar,
            'body_en' => $this->body_en,
            'warning_ar' => $this->warning_ar,
            'warning_en' => $this->warning_en,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'category' => new RadiologyScanCategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
