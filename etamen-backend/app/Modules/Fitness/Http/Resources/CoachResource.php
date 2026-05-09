<?php

namespace App\Modules\Fitness\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoachResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'slug' => $this->slug,
            'phone' => $this->phone,
            'email' => $this->email,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'coach_profile' => $this->whenLoaded('coachProfile', fn () => $this->coachProfile ? [
                'id' => $this->coachProfile->id,
                'coach_type' => $this->coachProfile->coach_type?->value,
                'experience_years' => $this->coachProfile->experience_years,
                'session_price' => $this->coachProfile->session_price,
                'monthly_followup_price' => $this->coachProfile->monthly_followup_price,
                'online_coaching_enabled' => $this->coachProfile->online_coaching_enabled,
                'gym_visit_enabled' => $this->coachProfile->gym_visit_enabled,
                'home_training_enabled' => $this->coachProfile->home_training_enabled,
                'certifications_summary' => $this->coachProfile->certifications_summary,
            ] : null),
            'session_types_count' => $this->whenCounted('coachSessionTypes'),
            'availability_count' => $this->whenCounted('coachAvailabilitySlots'),
            'packages_count' => $this->whenCounted('coachPackages'),
        ];
    }
}
