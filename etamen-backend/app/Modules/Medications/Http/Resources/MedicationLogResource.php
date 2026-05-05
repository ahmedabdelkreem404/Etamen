<?php

namespace App\Modules\Medications\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicationLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'medication_reminder_id' => $this->medication_reminder_id,
            'patient_user_id' => $this->patient_user_id,
            'scheduled_for' => $this->scheduled_for?->toISOString(),
            'action' => $this->action->value,
            'taken_at' => $this->taken_at?->toISOString(),
            'notes' => $this->notes,
            'metadata' => $this->metadata,
            'disclaimer' => MedicationReminderResource::DISCLAIMER,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
