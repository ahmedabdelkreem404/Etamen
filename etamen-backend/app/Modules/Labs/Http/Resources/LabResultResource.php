<?php

namespace App\Modules\Labs\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'uploaded_by' => $this->uploaded_by,
            'title_ar' => $this->title_ar,
            'title_en' => $this->title_en,
            'notes' => $this->notes,
            'status' => $this->status->value,
            'file' => $this->whenLoaded('file', fn () => [
                'id' => $this->file->id,
                'original_name' => $this->file->original_name,
                'mime_type' => $this->file->mime_type,
                'size' => $this->file->size,
                'visibility' => $this->file->visibility->value,
            ]),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
