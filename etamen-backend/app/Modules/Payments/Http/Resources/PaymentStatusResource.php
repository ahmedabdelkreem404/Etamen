<?php

namespace App\Modules\Payments\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentStatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status->value,
            'payment_method' => $this->paymentMethod ? [
                'id' => $this->paymentMethod->id,
                'type' => $this->paymentMethod->type->value,
                'name_ar' => $this->paymentMethod->name_ar,
                'name_en' => $this->paymentMethod->name_en,
            ] : null,
            'expires_at' => $this->expires_at?->toISOString(),
            'verified_at' => $this->verified_at?->toISOString(),
            'rejected_at' => $this->rejected_at?->toISOString(),
        ];
    }
}
