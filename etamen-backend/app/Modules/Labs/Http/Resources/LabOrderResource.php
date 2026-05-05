<?php

namespace App\Modules\Labs\Http\Resources;

use App\Modules\Payments\Http\Resources\PaymentStatusResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $canViewProviderFinancials = $this->canViewProviderFinancials($request);

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'patient_user_id' => $this->patient_user_id,
            'lab_provider_id' => $this->lab_provider_id,
            'payment_id' => $this->payment_id,
            'subtotal' => $this->subtotal,
            'discount_total' => $this->discount_total,
            'commission_amount' => $this->when($canViewProviderFinancials, $this->commission_amount),
            'provider_net_amount' => $this->when($canViewProviderFinancials, $this->provider_net_amount),
            'grand_total' => $this->grand_total,
            'currency' => $this->currency,
            'payment_status' => $this->payment_status->value,
            'order_status' => $this->order_status->value,
            'sample_collection_method' => $this->sample_collection_method->value,
            'collection_address' => $this->collection_address,
            'scheduled_at' => $this->scheduled_at?->toISOString(),
            'paid_at' => $this->paid_at?->toISOString(),
            'accepted_at' => $this->accepted_at?->toISOString(),
            'sample_collected_at' => $this->sample_collected_at?->toISOString(),
            'result_ready_at' => $this->result_ready_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'notes' => $this->notes,
            'items' => LabOrderItemResource::collection($this->whenLoaded('items')),
            'results' => LabResultResource::collection($this->whenLoaded('results')),
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
            ->whereKey($this->lab_provider_id)
            ->exists();
    }
}
