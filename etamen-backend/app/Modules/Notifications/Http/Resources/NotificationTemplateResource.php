<?php

namespace App\Modules\Notifications\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'category' => $this->category->value,
            'channel' => $this->channel->value,
            'title_ar' => $this->title_ar,
            'title_en' => $this->title_en,
            'body_ar' => $this->body_ar,
            'body_en' => $this->body_en,
            'is_active' => (bool) $this->is_active,
            'variables' => $this->variables,
            'metadata' => $this->metadata,
        ];
    }
}
