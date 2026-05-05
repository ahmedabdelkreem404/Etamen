<?php

namespace App\Modules\Health\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VitalTrendResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'vital_type' => $this['vital_type'],
            'unit' => $this['unit'],
            'range' => $this['range'],
            'points' => $this['points'],
            'latest_record' => $this['latest_record'] ? new VitalRecordResource($this['latest_record']) : null,
            'flags_summary' => $this['flags_summary'],
            'safe_disclaimer' => 'هذه المؤشرات للتتبع فقط وليست تشخيصًا أو خطة علاج.',
        ];
    }
}
