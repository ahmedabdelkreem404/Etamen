<?php

namespace App\Modules\AI\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiProviderConfigResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $config = is_array($this->encrypted_config) ? $this->encrypted_config : [];

        return [
            'id' => $this->id,
            'provider' => $this->provider->value,
            'is_active' => (bool) $this->is_active,
            'model' => $this->model,
            'safety_level' => $this->safety_level->value,
            'has_config' => $config !== [],
            'config_fields_count' => count($config),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
