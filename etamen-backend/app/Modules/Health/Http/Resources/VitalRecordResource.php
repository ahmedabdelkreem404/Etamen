<?php

namespace App\Modules\Health\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VitalRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_user_id' => $this->patient_user_id,
            'vital_type' => $this->vital_type->value,
            'measured_at' => $this->measured_at?->toISOString(),
            'value_decimal' => $this->value_decimal,
            'value_secondary_decimal' => $this->value_secondary_decimal,
            'unit' => $this->unit,
            'source' => $this->source->value,
            'flag' => $this->flag->value,
            'notes' => $this->notes,
            'metadata' => $this->metadata,
            'safety_note' => $this->safetyNote(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    private function safetyNote(): ?string
    {
        if (in_array($this->flag->value, ['very_low', 'low', 'high', 'very_high'], true)) {
            return 'هذه قراءة تحتاج مراجعة طبيب إذا كانت متكررة أو مصحوبة بأعراض. لو عندك أعراض خطيرة مثل ألم صدر أو ضيق تنفس، تواصل مع الطوارئ فورًا.';
        }

        return null;
    }
}
