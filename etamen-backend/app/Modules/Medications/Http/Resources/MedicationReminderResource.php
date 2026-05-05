<?php

namespace App\Modules\Medications\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicationReminderResource extends JsonResource
{
    public const DISCLAIMER = 'تذكيرات الأدوية للتنظيم فقط وليست وصفة طبية أو نصيحة علاجية.';

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_user_id' => $this->patient_user_id,
            'medication_name' => $this->medication_name,
            'dosage' => $this->dosage,
            'dosage_unit' => $this->dosage_unit,
            'instructions' => $this->instructions,
            'frequency_type' => $this->frequency_type->value,
            'interval_hours' => $this->interval_hours,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'timezone' => $this->timezone,
            'status' => $this->status->value,
            'prescribed_by' => $this->prescribed_by,
            'notes' => $this->notes,
            'refill_enabled' => $this->refill_enabled,
            'refill_quantity' => $this->refill_quantity,
            'refill_threshold' => $this->refill_threshold,
            'refill_reminder_date' => $this->refill_reminder_date?->toDateString(),
            'source' => $this->source->value,
            'metadata' => $this->metadata,
            'times' => MedicationReminderTimeResource::collection($this->whenLoaded('times')),
            'disclaimer' => self::DISCLAIMER,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
