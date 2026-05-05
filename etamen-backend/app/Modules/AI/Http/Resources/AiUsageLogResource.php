<?php

namespace App\Modules\AI\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiUsageLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_user_id' => $this->patient_user_id,
            'conversation_id' => $this->conversation_id,
            'provider' => $this->provider->value,
            'model' => $this->model,
            'prompt_tokens' => $this->prompt_tokens,
            'completion_tokens' => $this->completion_tokens,
            'total_tokens' => $this->total_tokens,
            'latency_ms' => $this->latency_ms,
            'success' => (bool) $this->success,
            'error_code' => $this->error_code,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
