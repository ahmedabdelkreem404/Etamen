<?php

namespace App\Modules\AdminOperations\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminPaymentReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $proof = $this->relationLoaded('proofs') ? $this->proofs->sortByDesc('id')->first() : null;
        $file = $proof?->relationLoaded('file') ? $proof->file : null;

        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status->value,
            'provider_id' => $this->provider_id,
            'provider_type' => $this->provider_type,
            'patient' => $this->whenLoaded('user', fn () => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ]),
            'provider' => $this->whenLoaded('provider', fn () => $this->provider ? [
                'id' => $this->provider->id,
                'type' => $this->provider->type->value,
                'name_ar' => $this->provider->name_ar,
                'name_en' => $this->provider->name_en,
            ] : null),
            'payment_method' => $this->paymentMethod ? [
                'id' => $this->paymentMethod->id,
                'type' => $this->paymentMethod->type->value,
                'name_ar' => $this->paymentMethod->name_ar,
                'name_en' => $this->paymentMethod->name_en,
            ] : null,
            'payable' => [
                'type' => class_basename((string) $this->payable_type),
                'id' => $this->payable_id,
                'number' => $this->payable?->appointment_number
                    ?? $this->payable?->order_number
                    ?? $this->payable?->booking_number
                    ?? null,
                'status' => $this->payable?->status?->value
                    ?? $this->payable?->order_status?->value
                    ?? $this->payable?->payment_status?->value
                    ?? null,
            ],
            'proof' => $proof ? [
                'exists' => true,
                'id' => $proof->id,
                'status' => $proof->status->value,
                'uploaded_at' => $proof->created_at?->toISOString(),
                'filename' => $file?->original_name,
                'mime_type' => $file?->mime_type,
                'size' => $file?->size,
            ] : ['exists' => false],
            'created_at' => $this->created_at?->toISOString(),
            'verified_at' => $this->verified_at?->toISOString(),
            'rejected_at' => $this->rejected_at?->toISOString(),
        ];
    }
}
