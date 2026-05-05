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

        return $this->sanitize($metadata);
    }

    private function sanitize(array $metadata): array
    {
        foreach ($metadata as $key => $value) {
            $normalizedKey = strtolower((string) $key);

            if (
                str_contains($normalizedKey, 'key')
                || str_contains($normalizedKey, 'secret')
                || str_contains($normalizedKey, 'token')
                || str_contains($normalizedKey, 'authorization')
                || str_contains($normalizedKey, 'raw')
                || str_contains($normalizedKey, 'content')
                || str_contains($normalizedKey, 'response')
            ) {
                unset($metadata[$key]);

                continue;
            }

            if (is_array($value)) {
                $metadata[$key] = $this->sanitize($value);
            }
        }

        return $metadata;
    }
}
