<?php

namespace App\Modules\Wallets\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'wallet_id' => $this->wallet_id,
            'source_type' => class_basename((string) $this->source_type),
            'source_id' => $this->source_id,
            'type' => $this->type->value,
            'gross_amount' => $this->gross_amount,
            'commission_amount' => $this->commission_amount,
            'net_amount' => $this->net_amount,
            'status' => $this->status->value,
            'description' => $this->description,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
