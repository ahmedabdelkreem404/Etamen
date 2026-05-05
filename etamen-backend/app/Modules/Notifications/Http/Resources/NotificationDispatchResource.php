<?php

namespace App\Modules\Notifications\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationDispatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'notification_id' => $this->notification_id,
            'user_id' => $this->user_id,
            'channel' => $this->channel->value,
            'provider' => $this->provider,
            'category' => $this->category->value,
            'type' => $this->type,
            'recipient' => $this->recipient ? '[hidden]' : null,
            'title' => $this->title,
            'body' => $this->body,
            'payload' => $this->payload,
            'status' => $this->status->value,
            'scheduled_for' => $this->scheduled_for?->toISOString(),
            'attempted_at' => $this->attempted_at?->toISOString(),
            'sent_at' => $this->sent_at?->toISOString(),
            'failure_reason' => $this->failure_reason,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
