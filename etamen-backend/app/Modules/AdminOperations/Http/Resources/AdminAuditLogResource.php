<?php

namespace App\Modules\AdminOperations\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminAuditLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'actor_id' => $this->actor_id,
            'actor_name' => $this->actor?->name,
            'event' => $this->action,
            'entity_type' => class_basename((string) $this->target_type),
            'entity_id' => $this->target_id,
            'safe_summary' => $this->safeSummary(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    private function safeSummary(): array
    {
        $metadata = collect($this->metadata ?? [])
            ->except(['path', 'file_path', 'storage_path', 'private_path', 'token', 'secret', 'password', 'config'])
            ->all();

        return [
            'action' => $this->action,
            'metadata' => $metadata,
        ];
    }
}
