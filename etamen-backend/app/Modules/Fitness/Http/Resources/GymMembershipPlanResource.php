<?php

namespace App\Modules\Fitness\Http\Resources;

use App\Modules\Providers\Http\Resources\ProviderBranchResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GymMembershipPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider_id' => $this->provider_id,
            'branch_id' => $this->branch_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'duration_days' => $this->duration_days,
            'price' => $this->price,
            'sessions_count' => $this->sessions_count,
            'includes_classes' => $this->includes_classes,
            'includes_personal_training' => $this->includes_personal_training,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'branch' => new ProviderBranchResource($this->whenLoaded('branch')),
        ];
    }
}
