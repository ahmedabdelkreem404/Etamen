<?php

namespace App\Modules\Notifications\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchedulerRunResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'job_name' => $this->job_name,
            'status' => $this->status->value,
            'started_at' => $this->started_at?->toISOString(),
            'finished_at' => $this->finished_at?->toISOString(),
            'processed_count' => $this->processed_count,
            'failed_count' => $this->failed_count,
            'metadata' => $this->metadata,
            'error_message' => $this->error_message,
        ];
    }
}
