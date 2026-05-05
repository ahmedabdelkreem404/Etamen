<?php

namespace App\Modules\Health\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HealthAccessLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_user_id' => $this->patient_user_id,
            'actor_id' => $this->actor_id,
            'action' => $this->action,
            'target_type' => $this->target_type ? class_basename($this->target_type) : null,
            'target_id' => $this->target_id,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
