<?php

namespace App\Modules\Providers\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderResource extends JsonResource
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
            'status' => $this->status->value,
            'is_active' => $this->is_active,
            'doctor_profile' => $this->whenLoaded('doctorProfile', fn () => $this->doctorProfile ? [
                'id' => $this->doctorProfile->id,
                'title' => $this->doctorProfile->title,
                'bio_ar' => $this->doctorProfile->bio_ar,
                'bio_en' => $this->doctorProfile->bio_en,
                'consultation_fee' => $this->doctorProfile->consultation_fee,
                'years_of_experience' => $this->doctorProfile->years_of_experience,
                'specialties' => SpecialtyResource::collection($this->doctorProfile->specialties),
            ] : null),
            'pharmacy_profile' => $this->whenLoaded('pharmacyProfile', fn () => $this->pharmacyProfile ? [
                'id' => $this->pharmacyProfile->id,
                'license_number' => $this->pharmacyProfile->license_number,
                'delivery_available' => $this->pharmacyProfile->delivery_available,
            ] : null),
            'lab_profile' => $this->whenLoaded('labProfile', fn () => $this->labProfile ? [
                'id' => $this->labProfile->id,
                'license_number' => $this->labProfile->license_number,
                'home_collection_available' => $this->labProfile->home_collection_available,
            ] : null),
            'branches' => ProviderBranchResource::collection($this->whenLoaded('branches')),
            'approval_requests' => ProviderApprovalRequestResource::collection($this->whenLoaded('approvalRequests')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
