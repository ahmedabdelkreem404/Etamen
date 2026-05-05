<?php

namespace App\Modules\AI\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiContextPreviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'context' => $this->resource,
            'privacy_note' => 'AI context is limited to safe patient-owned health summary fields only.',
        ];
    }
}
