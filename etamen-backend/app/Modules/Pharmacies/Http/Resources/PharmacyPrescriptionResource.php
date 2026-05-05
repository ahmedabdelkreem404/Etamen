<?php

namespace App\Modules\Pharmacies\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PharmacyPrescriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_user_id' => $this->patient_user_id,
            'pharmacy_provider_id' => $this->pharmacy_provider_id,
            'file' => $this->whenLoaded('uploadedFile', fn () => [
                'id' => $this->uploadedFile->id,
                'original_name' => $this->uploadedFile->original_name,
                'mime_type' => $this->uploadedFile->mime_type,
                'size' => $this->uploadedFile->size,
                'category' => $this->uploadedFile->file_category->value,
                'visibility' => $this->uploadedFile->visibility->value,
            ]),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
