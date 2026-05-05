<?php

namespace App\Modules\Medications\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicationAdherenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'from' => $this['from'],
            'to' => $this['to'],
            'total_scheduled' => $this['total_scheduled'],
            'taken_count' => $this['taken_count'],
            'skipped_count' => $this['skipped_count'],
            'missed_count' => $this['missed_count'],
            'adherence_percentage' => $this['adherence_percentage'],
            'as_needed_count' => $this['as_needed_count'],
            'by_reminder' => $this['by_reminder'],
            'disclaimer' => $this['disclaimer'],
        ];
    }
}
