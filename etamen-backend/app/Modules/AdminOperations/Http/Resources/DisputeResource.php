<?php

namespace App\Modules\AdminOperations\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DisputeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'dispute_number' => $this->dispute_number,
            'provider_id' => $this->provider_id,
            'payment_id' => $this->payment_id,
            'context_type' => $this->context_type,
            'context_id' => $this->context_id,
            'reason' => $this->reason,
            'status' => $this->status,
            'priority' => $this->priority,
            'user' => $this->whenLoaded('user', fn () => [
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
            'payment' => $this->whenLoaded('payment', fn () => $this->payment ? [
                'id' => $this->payment->id,
                'status' => $this->payment->status->value,
                'amount' => $this->payment->amount,
                'currency' => $this->payment->currency,
            ] : null),
            'resolved_at' => $this->resolved_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
