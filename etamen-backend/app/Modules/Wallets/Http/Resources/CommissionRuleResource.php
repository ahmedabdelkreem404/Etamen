<?php

namespace App\Modules\Wallets\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommissionRuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider_type' => $this->provider_type->value,
            'service_type' => $this->service_type->value,
            'percentage' => $this->percentage,
            'fixed_amount' => $this->fixed_amount,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'is_active' => $this->is_active,
        ];
    }
}
