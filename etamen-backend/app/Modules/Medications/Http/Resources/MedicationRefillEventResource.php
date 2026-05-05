<?php

namespace App\Modules\Medications\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicationRefillEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'medication_reminder_id' => $this->medication_reminder_id,
            'patient_user_id' => $this->patient_user_id,
            'event_type' => $this->event_type->value,
            'event_date' => $this->event_date?->toDateString(),
            'notes' => $this->notes,
            'disclaimer' => MedicationReminderResource::DISCLAIMER,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
