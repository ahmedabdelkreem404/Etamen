<?php

namespace App\Modules\Payments\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentProofResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_id' => $this->payment_id,
            'reference_number' => $this->reference_number,
            'sender_phone' => $this->sender_phone,
            'notes' => $this->notes,
            'status' => $this->status->value,
            'reviewed_at' => $this->reviewed_at?->toISOString(),
            'rejection_reason' => $this->rejection_reason,
            'file' => $this->whenLoaded('file', fn () => [
                'id' => $this->file->id,
                'original_name' => $this->file->original_name,
                'mime_type' => $this->file->mime_type,
                'size' => $this->file->size,
                'file_category' => $this->file->file_category->value,
                'visibility' => $this->file->visibility->value,
            ]),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
