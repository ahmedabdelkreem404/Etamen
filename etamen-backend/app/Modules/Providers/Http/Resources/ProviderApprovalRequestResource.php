<?php

namespace App\Modules\Providers\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderApprovalRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider_id' => $this->provider_id,
            'requested_by' => $this->requested_by,
            'reviewed_by' => $this->reviewed_by,
            'status' => $this->status->value,
            'notes' => $this->notes,
            'review_notes' => $this->review_notes,
            'reviewed_at' => $this->reviewed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
