<?php

namespace App\Modules\Notifications\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationPreferenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'channel' => $this->channel->value,
            'category' => $this->category->value,
            'is_enabled' => (bool) $this->is_enabled,
            'quiet_hours_start' => $this->quiet_hours_start ? substr((string) $this->quiet_hours_start, 0, 5) : null,
            'quiet_hours_end' => $this->quiet_hours_end ? substr((string) $this->quiet_hours_end, 0, 5) : null,
            'timezone' => $this->timezone,
            'metadata' => $this->metadata,
        ];
    }
}
