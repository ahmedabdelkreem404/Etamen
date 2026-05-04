<?php

namespace App\Modules\Payments\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'instructions_ar' => $this->instructions_ar,
            'instructions_en' => $this->instructions_en,
            'sort_order' => $this->sort_order,
        ];
    }
}
