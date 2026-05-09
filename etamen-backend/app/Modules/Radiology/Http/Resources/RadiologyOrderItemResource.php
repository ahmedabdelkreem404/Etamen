<?php

namespace App\Modules\Radiology\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RadiologyOrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'radiology_scan_id' => $this->radiology_scan_id,
            'scan_name_ar' => $this->scan_name_ar,
            'scan_name_en' => $this->scan_name_en,
            'category_name_ar' => $this->category_name_ar,
            'category_name_en' => $this->category_name_en,
            'unit_price' => $this->unit_price,
            'quantity' => $this->quantity,
            'total_price' => $this->total_price,
            'preparation_snapshot_ar' => $this->preparation_snapshot_ar,
            'preparation_snapshot_en' => $this->preparation_snapshot_en,
        ];
    }
}
