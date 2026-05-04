<?php

namespace App\Modules\Providers\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider_id' => $this->provider_id,
            'document_type' => $this->document_type,
            'status' => $this->status->value,
            'notes' => $this->notes,
            'file' => $this->whenLoaded('file', fn () => [
                'id' => $this->file->id,
                'original_name' => $this->file->original_name,
                'mime_type' => $this->file->mime_type,
                'size' => $this->file->size,
                'category' => $this->file->file_category->value,
                'visibility' => $this->file->visibility->value,
            ]),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
