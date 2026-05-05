<?php

namespace App\Modules\Notifications\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationTokenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'provider' => $this->provider->value,
            'device_type' => $this->device_type->value,
            'device_name' => $this->device_name,
            'app_version' => $this->app_version,
            'locale' => $this->locale,
            'timezone' => $this->timezone,
            'is_active' => (bool) $this->is_active,
            'last_seen_at' => $this->last_seen_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
