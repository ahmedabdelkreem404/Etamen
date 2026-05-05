<?php

namespace App\Modules\AI\Http\Resources;

use App\Modules\AI\Application\Services\AiSafetyGuardService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_user_id' => $this->patient_user_id,
            'title' => $this->title,
            'status' => $this->status->value,
            'provider' => $this->provider->value,
            'language' => $this->language->value,
            'context_enabled' => (bool) $this->context_enabled,
            'safety_level' => $this->safety_level->value,
            'last_message_at' => $this->last_message_at?->toISOString(),
            'metadata' => $this->metadata,
            'messages_count' => $this->when(isset($this->messages_count), $this->messages_count),
            'safety_disclaimer' => $this->language->value === 'en'
                ? AiSafetyGuardService::EN_SAFETY_LINE
                : AiSafetyGuardService::AR_SAFETY_LINE,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
