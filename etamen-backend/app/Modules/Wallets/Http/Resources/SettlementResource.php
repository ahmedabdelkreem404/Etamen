<?php

namespace App\Modules\Wallets\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettlementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider_id' => $this->provider_id,
            'provider_type' => $this->provider_type->value,
            'total_gross' => $this->total_gross,
            'total_commission' => $this->total_commission,
            'total_net' => $this->total_net,
            'status' => $this->status->value,
            'settled_by' => $this->settled_by,
            'settled_at' => $this->settled_at?->toISOString(),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'wallet_transaction_id' => $item->wallet_transaction_id,
                'amount' => $item->amount,
            ])->values()),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
