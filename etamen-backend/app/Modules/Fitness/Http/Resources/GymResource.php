<?php

namespace App\Modules\Fitness\Http\Resources;

use App\Modules\Providers\Http\Resources\ProviderBranchResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GymResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $mainBranch = $this->relationLoaded('branches')
            ? $this->branches->sortByDesc('is_main')->first()
            : null;

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
            'primary_branch_name' => $mainBranch?->name_ar ?? $mainBranch?->name_en,
            'primary_area_name' => $mainBranch?->area?->name_ar ?? $mainBranch?->area?->name_en,
            'primary_city_name' => $mainBranch?->city?->name_ar ?? $mainBranch?->city?->name_en,
            'gym_profile' => $this->whenLoaded('gymProfile', fn () => $this->gymProfile ? [
                'id' => $this->gymProfile->id,
                'men_allowed' => $this->gymProfile->men_allowed,
                'women_allowed' => $this->gymProfile->women_allowed,
                'ladies_only_hours' => $this->gymProfile->ladies_only_hours,
                'has_classes' => $this->gymProfile->has_classes,
                'has_personal_training' => $this->gymProfile->has_personal_training,
                'description_ar' => $this->gymProfile->description_ar,
                'description_en' => $this->gymProfile->description_en,
            ] : null),
            'branches' => ProviderBranchResource::collection($this->whenLoaded('branches')),
            'membership_plans_count' => $this->whenCounted('gymMembershipPlans'),
            'classes_count' => $this->whenCounted('gymClasses'),
        ];
    }
}
