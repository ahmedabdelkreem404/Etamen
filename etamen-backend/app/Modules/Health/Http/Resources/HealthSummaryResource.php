<?php

namespace App\Modules\Health\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HealthSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'profile_completion_percentage' => $this['profile_completion_percentage'],
            'latest_vitals' => VitalRecordResource::collection($this['latest_vitals']),
            'active_chronic_diseases_count' => $this['active_chronic_diseases_count'],
            'active_allergies_count' => $this['active_allergies_count'],
            'active_current_medications_count' => $this['active_current_medications_count'],
            'active_goals_count' => $this['active_goals_count'],
            'non_diagnostic_warning_flags_count' => $this['non_diagnostic_warning_flags_count'],
            'bmi' => $this['bmi'],
            'safe_disclaimer' => $this['safe_disclaimer'],
        ];
    }
}
