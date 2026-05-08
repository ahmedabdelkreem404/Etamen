<?php

namespace App\Modules\Providers\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ProviderProfileService
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function currentProviderFor(User $user): Provider
    {
        $provider = $user->ownedProviders()
            ->with($this->providerRelations())
            ->first();

        if (! $provider) {
            throw (new ModelNotFoundException)->setModel(Provider::class);
        }

        return $provider;
    }

    public function updateOwnedProvider(User $user, array $data): Provider
    {
        return DB::transaction(function () use ($user, $data): Provider {
            $provider = $this->currentProviderFor($user);
            $before = $provider->getAttributes();

            $provider->fill(collect($data)->only([
                'name_ar',
                'name_en',
                'phone',
                'email',
                'description_ar',
                'description_en',
            ])->all());
            $provider->save();

            $profileData = $data['profile'] ?? [];
            match ($provider->type) {
                ProviderType::Doctor => $this->updateDoctorProfile($provider, $profileData),
                ProviderType::Pharmacy => $provider->pharmacyProfile?->update(collect($profileData)->only(['license_number', 'delivery_available'])->all()),
                ProviderType::Lab => $provider->labProfile?->update(collect($profileData)->only(['license_number', 'home_collection_available'])->all()),
                ProviderType::Hospital => $provider->hospitalProfile?->update(collect($profileData)->only(['license_number', 'description_ar', 'description_en', 'emergency_available', 'has_inpatient', 'has_outpatient', 'has_icu', 'has_ambulance'])->all()),
                ProviderType::Clinic => $provider->clinicProfile?->update(collect($profileData)->only(['clinic_type', 'description_ar', 'description_en'])->all()),
                ProviderType::MedicalCenter => $provider->medicalCenterProfile?->update(collect($profileData)->only(['center_type', 'description_ar', 'description_en'])->all()),
                ProviderType::Radiology => $provider->radiologyProfile?->update(collect($profileData)->only(['license_number', 'home_service_enabled', 'report_delivery_enabled', 'dicom_supported', 'description_ar', 'description_en'])->all()),
                ProviderType::Gym => $provider->gymProfile?->update(collect($profileData)->only(['men_allowed', 'women_allowed', 'ladies_only_hours', 'has_classes', 'has_personal_training', 'description_ar', 'description_en'])->all()),
                ProviderType::FitnessCoach, ProviderType::NutritionCoach => $provider->coachProfile?->update(collect($profileData)->only(['coach_type', 'experience_years', 'session_price', 'monthly_followup_price', 'online_coaching_enabled', 'gym_visit_enabled', 'home_training_enabled', 'certifications_summary'])->all()),
                ProviderType::Physiotherapy => $provider->physiotherapyProfile?->update(collect($profileData)->only(['home_visit_enabled', 'center_visit_enabled', 'session_price', 'description_ar', 'description_en'])->all()),
                ProviderType::HomeHealthcare => $provider->homeHealthcareProfile?->update(collect($profileData)->only(['nursing_enabled', 'injections_enabled', 'wound_care_enabled', 'elderly_care_enabled', 'physiotherapy_home_enabled', 'service_radius_km', 'description_ar', 'description_en'])->all()),
            };

            $this->auditLogService->log('provider.profile_updated', $provider, $user, before: $before, after: $provider->getAttributes());

            return $provider->refresh()->load($this->providerRelations());
        });
    }

    private function updateDoctorProfile(Provider $provider, array $profileData): void
    {
        $doctorProfile = $provider->doctorProfile;

        if (! $doctorProfile) {
            return;
        }

        $doctorProfile->update(collect($profileData)->only([
            'title',
            'bio_ar',
            'bio_en',
            'consultation_fee',
            'years_of_experience',
        ])->all());

        if (array_key_exists('specialty_ids', $profileData)) {
            $doctorProfile->specialties()->sync($profileData['specialty_ids'] ?? []);
        }
    }

    private function providerRelations(): array
    {
        return [
            'doctorProfile.specialties',
            'pharmacyProfile',
            'labProfile',
            'hospitalProfile',
            'clinicProfile',
            'medicalCenterProfile',
            'radiologyProfile',
            'gymProfile',
            'coachProfile',
            'physiotherapyProfile',
            'homeHealthcareProfile',
            'bookingSettings',
            'activeContract',
            'branches.city',
            'branches.area',
        ];
    }
}
