<?php

namespace App\Modules\Notifications\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'category' => $this->category->value,
            'type' => $this->type,
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
            'priority' => $this->priority->value,
            'read_at' => $this->read_at?->toISOString(),
            'action_url' => $this->action_url,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
