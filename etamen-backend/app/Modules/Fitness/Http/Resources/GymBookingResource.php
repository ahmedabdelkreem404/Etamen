<?php

namespace App\Modules\Fitness\Http\Resources;

use App\Modules\Payments\Http\Resources\PaymentStatusResource;
use App\Modules\Providers\Http\Resources\ProviderBranchResource;
use App\Modules\Providers\Http\Resources\ProviderResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GymBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_number' => $this->booking_number,
            'patient_user_id' => $this->patient_user_id,
            'provider_id' => $this->provider_id,
            'branch_id' => $this->branch_id,
            'membership_plan_id' => $this->membership_plan_id,
            'gym_class_id' => $this->gym_class_id,
            'status' => $this->status->value,
            'total_amount' => $this->total_amount,
            'payment_id' => $this->payment_id,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'notes' => $this->notes,
            'provider' => new ProviderResource($this->whenLoaded('provider')),
            'branch' => new ProviderBranchResource($this->whenLoaded('branch')),
            'membership_plan' => new GymMembershipPlanResource($this->whenLoaded('membershipPlan')),
            'gym_class' => new GymClassResource($this->whenLoaded('gymClass')),
            'payment' => new PaymentStatusResource($this->whenLoaded('payment')),
        ];
    }
}
