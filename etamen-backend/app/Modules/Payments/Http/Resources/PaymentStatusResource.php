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
            'payable' => [
                'type' => class_basename((string) $this->payable_type),
                'id' => $this->payable_id,
            ],
            'appointment' => $this->whenLoaded('payable', function () {
                if (! $this->payable || ! str_ends_with((string) $this->payable_type, 'Appointment')) {
                    return null;
                }

                return [
                    'id' => $this->payable->id,
                    'appointment_number' => $this->payable->appointment_number,
                    'status' => $this->payable->status->value,
                ];
            }),
            'pharmacy_order' => $this->whenLoaded('payable', function () {
                if (! $this->payable || ! str_ends_with((string) $this->payable_type, 'PharmacyOrder')) {
                    return null;
                }

                return [
                    'id' => $this->payable->id,
                    'order_number' => $this->payable->order_number,
                    'order_status' => $this->payable->order_status->value,
                    'payment_status' => $this->payable->payment_status->value,
                ];
            }),
            'payment_method' => $this->paymentMethod ? [
                'id' => $this->paymentMethod->id,
                'type' => $this->paymentMethod->type->value,
                'name_ar' => $this->paymentMethod->name_ar,
                'name_en' => $this->paymentMethod->name_en,
            ] : null,
            'invoice' => new InvoiceResource($this->whenLoaded('invoice')),
            'expires_at' => $this->expires_at?->toISOString(),
            'verified_at' => $this->verified_at?->toISOString(),
            'rejected_at' => $this->rejected_at?->toISOString(),
        ];
    }
}
