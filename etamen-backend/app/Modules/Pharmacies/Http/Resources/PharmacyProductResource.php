<?php

namespace App\Modules\Pharmacies\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PharmacyProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $metadata = is_array($this->metadata) ? $this->metadata : [];
        $stockQuantity = (int) $this->stock_quantity;

        return [
            'id' => $this->id,
            'provider_id' => $this->provider_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'sku' => $this->sku,
            'price' => $this->price,
            'image' => $this->whenLoaded('imageFile', fn () => $this->imageFile ? [
                'id' => $this->imageFile->id,
                'original_name' => $this->imageFile->original_name,
                'mime_type' => $this->imageFile->mime_type,
            ] : null),
            'requires_prescription' => $this->requires_prescription,
            'stock_quantity' => $this->stock_quantity,
            'in_stock' => $stockQuantity > 0,
            'stock_label_ar' => $stockQuantity > 0 ? 'Ù…ØªØ§Ø­' : 'ØºÙŠØ± Ù…ØªØ§Ø­',
            'stock_label_en' => $stockQuantity > 0 ? 'In stock' : 'Out of stock',
            'category' => $metadata['category'] ?? $metadata['category_ar'] ?? $metadata['category_en'] ?? null,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
