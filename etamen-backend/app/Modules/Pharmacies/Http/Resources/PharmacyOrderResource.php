<?php

namespace App\Modules\Pharmacies\Http\Resources;

use App\Modules\Payments\Http\Resources\PaymentStatusResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PharmacyOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $canViewProviderFinancials = $this->canViewProviderFinancials($request);

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'patient_user_id' => $this->patient_user_id,
            'pharmacy_provider_id' => $this->pharmacy_provider_id,
            'prescription_id' => $this->prescription_id,
            'payment_id' => $this->payment_id,
            'subtotal' => $this->subtotal,
            'discount_total' => $this->discount_total,
            'commission_amount' => $this->when($canViewProviderFinancials, $this->commission_amount),
            'provider_net_amount' => $this->when($canViewProviderFinancials, $this->provider_net_amount),
            'grand_total' => $this->grand_total,
            'currency' => $this->currency,
            'payment_status' => $this->payment_status->value,
            'order_status' => $this->order_status->value,
            'delivery_method' => $this->delivery_method->value,
            'delivery_address' => $this->delivery_address,
            'notes' => $this->notes,
            'paid_at' => $this->paid_at?->toISOString(),
            'delivered_at' => $this->delivered_at?->toISOString(),
            'stock_reserved_at' => $this->stock_reserved_at?->toISOString(),
            'stock_released_at' => $this->stock_released_at?->toISOString(),
            'items' => PharmacyOrderItemResource::collection($this->whenLoaded('items')),
            'prescription' => new PharmacyPrescriptionResource($this->whenLoaded('prescription')),
            'payment' => new PaymentStatusResource($this->whenLoaded('payment')),
            'status_histories' => $this->whenLoaded('statusHistories', fn () => $this->statusHistories->map(fn ($history) => [
                'from_status' => $history->from_status,
                'to_status' => $history->to_status,
                'actor_id' => $history->actor_id,
                'reason' => $history->reason,
                'created_at' => $history->created_at?->toISOString(),
            ])->values()),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    private function canViewProviderFinancials(Request $request): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        if ($user->isPlatformAdmin()) {
            return true;
        }

        return $user->ownedProviders()
            ->whereKey($this->pharmacy_provider_id)
            ->exists();
    }
}
