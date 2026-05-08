<?php

namespace App\Modules\Providers\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderPublicCertificateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_type' => $this->document_type,
            'status' => $this->status->value,
            'visibility' => $this->visibility->value,
            'file' => $this->whenLoaded('file', fn () => [
                'id' => $this->file->id,
                'original_name' => $this->file->original_name,
                'mime_type' => $this->file->mime_type,
                'size' => $this->file->size,
            ]),
            'approved_public_at' => $this->approved_public_at?->toISOString(),
        ];
    }
}
