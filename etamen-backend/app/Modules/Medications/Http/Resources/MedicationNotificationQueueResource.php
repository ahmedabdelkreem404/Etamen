<?php

namespace App\Modules\Medications\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicationNotificationQueueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'medication_reminder_id' => $this->medication_reminder_id,
            'patient_user_id' => $this->patient_user_id,
            'scheduled_for' => $this->scheduled_for?->toISOString(),
            'notification_type' => $this->notification_type->value,
            'status' => $this->status->value,
            'channel' => $this->channel->value,
            'payload' => $this->payload,
            'attempted_at' => $this->attempted_at?->toISOString(),
            'sent_at' => $this->sent_at?->toISOString(),
            'failure_reason' => $this->failure_reason,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
