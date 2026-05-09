<?php

namespace App\Modules\Radiology\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RadiologyResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'radiology_order_id' => $this->radiology_order_id,
            'result_type' => $this->result_type->value,
            'title_ar' => $this->title_ar,
            'title_en' => $this->title_en,
            'notes_ar' => $this->notes_ar,
            'notes_en' => $this->notes_en,
            'is_visible_to_patient' => $this->is_visible_to_patient,
            'file' => $this->whenLoaded('file', fn () => [
                'id' => $this->file->id,
                'original_name' => $this->file->original_name,
                'mime_type' => $this->file->mime_type,
                'size' => $this->file->size,
            ]),
            'download_url' => $this->when(
                $this->is_visible_to_patient || $request->user()?->isPlatformAdmin(),
                '/api/v1/radiology/results/'.$this->id.'/download',
            ),
            'uploaded_at' => $this->uploaded_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
