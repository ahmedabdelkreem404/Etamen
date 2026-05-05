<?php

namespace App\Modules\AI\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'patient_user_id' => $this->patient_user_id,
            'role' => $this->role->value,
            'content' => $this->content,
            'safety_classification' => $this->safety_classification->value,
            'was_refused' => (bool) $this->was_refused,
            'provider' => $this->provider?->value,
            'token_count' => $this->token_count,
            'metadata' => $this->safeMetadata(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    private function safeMetadata(): ?array
    {
        $metadata = $this->metadata;

        if (! is_array($metadata)) {
            return null;
        }

        unset($metadata['provider_metadata']['raw'], $metadata['api_key'], $metadata['secret']);

        return $metadata;
    }
}
