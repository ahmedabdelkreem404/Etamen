<?php

namespace App\Modules\Wallets\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'owner_type' => $this->owner_type->value,
            'owner_id' => $this->owner_id,
            'currency' => $this->currency,
            'status' => $this->status->value,
            'balances' => $this->additional['balances'] ?? null,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
