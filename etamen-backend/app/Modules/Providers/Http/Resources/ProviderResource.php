<?php

namespace App\Modules\Providers\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ProviderResource extends JsonResource
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
            'status' => $this->status->value,
            'is_active' => $this->is_active,
            'primary_branch_name' => $mainBranch?->name_ar ?? $mainBranch?->name_en,
            'primary_area_name' => $mainBranch?->area?->name_ar ?? $mainBranch?->area?->name_en,
            'primary_city_name' => $mainBranch?->city?->name_ar ?? $mainBranch?->city?->name_en,
            'doctor_profile' => $this->whenLoaded('doctorProfile', fn () => $this->doctorProfile ? [
                'id' => $this->doctorProfile->id,
                'title' => $this->doctorProfile->title,
                'bio_ar' => $this->doctorProfile->bio_ar,
                'bio_en' => $this->doctorProfile->bio_en,
                'avatar_url' => $this->publicAssetUrl($request, $this->doctorProfile->avatar_path),
                'rating_average' => $this->formatRatingAverage($this->doctorProfile->rating_average),
                'reviews_count' => (int) ($this->doctorProfile->reviews_count ?? 0),
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

    private function formatRatingAverage(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        return round((float) $value, 1);
    }

    private function publicAssetUrl(Request $request, ?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $path = ltrim(str_replace('\\', '/', $path), '/');

        if (
            Str::contains($path, ['..', '://']) ||
            Str::startsWith($path, ['storage/medical', 'medical-private', 'medical_private', 'private', 'provider-documents'])
        ) {
            return null;
        }

        return rtrim($request->getSchemeAndHttpHost(), '/').'/'.$path;
    }
}
