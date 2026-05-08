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
            'hospital_profile' => $this->profilePayload('hospitalProfile', [
                'license_number',
                'description_ar',
                'description_en',
                'emergency_available',
                'has_inpatient',
                'has_outpatient',
                'has_icu',
                'has_ambulance',
                'is_active',
            ]),
            'clinic_profile' => $this->profilePayload('clinicProfile', ['clinic_type', 'description_ar', 'description_en', 'is_active']),
            'medical_center_profile' => $this->profilePayload('medicalCenterProfile', ['center_type', 'description_ar', 'description_en', 'is_active']),
            'radiology_profile' => $this->profilePayload('radiologyProfile', [
                'license_number',
                'home_service_enabled',
                'report_delivery_enabled',
                'dicom_supported',
                'description_ar',
                'description_en',
                'is_active',
            ]),
            'gym_profile' => $this->profilePayload('gymProfile', [
                'men_allowed',
                'women_allowed',
                'ladies_only_hours',
                'has_classes',
                'has_personal_training',
                'description_ar',
                'description_en',
                'is_active',
            ]),
            'coach_profile' => $this->profilePayload('coachProfile', [
                'coach_type',
                'experience_years',
                'session_price',
                'monthly_followup_price',
                'online_coaching_enabled',
                'gym_visit_enabled',
                'home_training_enabled',
                'certifications_summary',
                'is_active',
            ]),
            'physiotherapy_profile' => $this->profilePayload('physiotherapyProfile', [
                'home_visit_enabled',
                'center_visit_enabled',
                'session_price',
                'description_ar',
                'description_en',
                'is_active',
            ]),
            'home_healthcare_profile' => $this->profilePayload('homeHealthcareProfile', [
                'nursing_enabled',
                'injections_enabled',
                'wound_care_enabled',
                'elderly_care_enabled',
                'physiotherapy_home_enabled',
                'service_radius_km',
                'description_ar',
                'description_en',
                'is_active',
            ]),
            'booking_capabilities' => $this->bookingCapabilities(),
            'payment_options' => $this->paymentOptions(),
            'public_certificates' => ProviderPublicCertificateResource::collection($this->whenLoaded('publicDocuments')),
            'services' => ProviderServiceResource::collection($this->whenLoaded('publicServices')),
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

    private function profilePayload(string $relation, array $fields): mixed
    {
        return $this->whenLoaded($relation, function () use ($relation, $fields) {
            $profile = $this->{$relation};

            if (! $profile) {
                return null;
            }

            return ['id' => $profile->id]
                + collect($fields)
                    ->mapWithKeys(fn (string $field): array => [$field => $profile->{$field}])
                    ->all();
        });
    }

    private function bookingCapabilities(): array
    {
        $settings = $this->relationLoaded('bookingSettings') ? $this->bookingSettings : null;

        return [
            'clinic_visit_enabled' => (bool) ($settings?->clinic_visit_enabled ?? true),
            'online_video_enabled' => (bool) ($settings?->online_video_enabled ?? false),
            'home_visit_enabled' => (bool) ($settings?->home_visit_enabled ?? false),
            'branch_visit_enabled' => (bool) ($settings?->branch_visit_enabled ?? true),
            'booking_requires_payment' => (bool) ($settings?->booking_requires_payment ?? true),
            'pay_at_branch_enabled' => $this->payAtBranchEnabled(),
            'default_slot_duration_minutes' => $settings?->default_slot_duration_minutes,
        ];
    }

    private function paymentOptions(): array
    {
        $contract = $this->relationLoaded('activeContract') ? $this->activeContract : null;

        return [
            'online_payment_required' => (bool) ($contract?->online_payment_required ?? true),
            'pay_at_branch_enabled' => $this->payAtBranchEnabled(),
        ];
    }

    private function payAtBranchEnabled(): bool
    {
        $settings = $this->relationLoaded('bookingSettings') ? $this->bookingSettings : null;
        $contract = $this->relationLoaded('activeContract') ? $this->activeContract : null;

        return (bool) ($settings?->pay_at_branch_enabled ?? false)
            && (bool) ($contract?->pay_at_branch_allowed ?? false);
    }
}
