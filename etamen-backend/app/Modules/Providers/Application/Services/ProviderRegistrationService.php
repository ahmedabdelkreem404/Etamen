<?php

namespace App\Modules\Providers\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Providers\Domain\Enums\ApprovalRequestStatus;
use App\Modules\Providers\Domain\Enums\CoachType;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\ClinicProfile;
use App\Modules\Providers\Infrastructure\Models\CoachProfile;
use App\Modules\Providers\Infrastructure\Models\DoctorProfile;
use App\Modules\Providers\Infrastructure\Models\GymProfile;
use App\Modules\Providers\Infrastructure\Models\HomeHealthcareProfile;
use App\Modules\Providers\Infrastructure\Models\HospitalProfile;
use App\Modules\Providers\Infrastructure\Models\LabProfile;
use App\Modules\Providers\Infrastructure\Models\MedicalCenterProfile;
use App\Modules\Providers\Infrastructure\Models\PharmacyProfile;
use App\Modules\Providers\Infrastructure\Models\PhysiotherapyProfile;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderApprovalRequest;
use App\Modules\Providers\Infrastructure\Models\RadiologyProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ProviderRegistrationService
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function registerDoctor(array $data): array
    {
        return $this->register(
            ProviderType::Doctor,
            UserRole::Doctor,
            $data,
            fn (Provider $provider, User $user) => $this->createDoctorProfile($provider, $user, $data),
        );
    }

    public function registerPharmacy(array $data): array
    {
        return $this->register(
            ProviderType::Pharmacy,
            UserRole::PharmacyAdmin,
            $data,
            fn (Provider $provider, User $user) => PharmacyProfile::query()->create([
                'provider_id' => $provider->id,
                'license_number' => $data['license_number'] ?? null,
                'delivery_available' => (bool) ($data['delivery_available'] ?? false),
            ]),
        );
    }

    public function registerLab(array $data): array
    {
        return $this->register(
            ProviderType::Lab,
            UserRole::LabAdmin,
            $data,
            fn (Provider $provider, User $user) => LabProfile::query()->create([
                'provider_id' => $provider->id,
                'license_number' => $data['license_number'] ?? null,
                'home_collection_available' => (bool) ($data['home_collection_available'] ?? false),
            ]),
        );
    }

    public function registerGeneric(array $data): array
    {
        $type = ProviderType::from($data['provider_type']);

        return $this->register(
            $type,
            $this->roleForProviderType($type),
            $data,
            fn (Provider $provider, User $user) => $this->createTypeProfile($provider, $user, $data),
        );
    }

    private function register(ProviderType $type, UserRole $role, array $data, callable $profileFactory): array
    {
        return DB::transaction(function () use ($type, $role, $data, $profileFactory): array {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            Role::findOrCreate($role->value);
            $user->assignRole($role->value);

            $provider = Provider::query()->create([
                'type' => $type,
                'owner_user_id' => $user->id,
                'name_ar' => $data['provider_name_ar'] ?? null,
                'name_en' => $data['provider_name_en'],
                'phone' => $data['phone'] ?? null,
                'email' => $data['provider_email'] ?? $data['email'],
                'description_ar' => $data['description_ar'] ?? null,
                'description_en' => $data['description_en'] ?? null,
                'status' => ProviderStatus::PendingReview,
                'is_active' => false,
                'created_by' => $user->id,
            ]);

            $provider->staff()->create([
                'user_id' => $user->id,
                'role' => ProviderStaffRole::Owner,
                'is_owner' => true,
                'status' => 'active',
            ]);

            $profile = $profileFactory($provider, $user);

            if (! empty($data['branch_name_en'])) {
                $provider->branches()->create([
                    'name_ar' => $data['branch_name_ar'] ?? null,
                    'name_en' => $data['branch_name_en'],
                    'phone' => $data['branch_phone'] ?? $data['phone'] ?? null,
                    'whatsapp' => $data['branch_whatsapp'] ?? null,
                    'city_id' => $data['city_id'] ?? null,
                    'area_id' => $data['area_id'] ?? null,
                    'address_line_1' => $data['address_line_1'] ?? null,
                    'address_line_2' => $data['address_line_2'] ?? null,
                    'district' => $data['district'] ?? null,
                    'address_ar' => $data['address_ar'] ?? null,
                    'address_en' => $data['address_en'] ?? null,
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null,
                    'working_hours_json' => $data['working_hours_json'] ?? null,
                    'is_24_hours' => (bool) ($data['is_24_hours'] ?? false),
                    'home_service_radius_km' => $data['home_service_radius_km'] ?? null,
                    'delivery_radius_km' => $data['delivery_radius_km'] ?? null,
                    'is_main' => true,
                    'is_active' => true,
                ]);
            }

            $approvalRequest = ProviderApprovalRequest::query()->create([
                'provider_id' => $provider->id,
                'requested_by' => $user->id,
                'status' => ApprovalRequestStatus::Pending,
                'notes' => $data['approval_notes'] ?? null,
            ]);

            $this->auditLogService->log('provider.registered', $provider, $user, metadata: ['type' => $type->value]);
            $this->auditLogService->log('provider_approval_request.created', $approvalRequest, $user);

            return [
                'user' => $user->refresh(),
                'provider' => $provider->refresh(),
                'profile' => $profile,
                'token' => $user->createToken('provider-registration')->plainTextToken,
                'token_type' => 'Bearer',
            ];
        });
    }

    private function createDoctorProfile(Provider $provider, User $user, array $data): DoctorProfile
    {
        $profile = DoctorProfile::query()->create([
            'provider_id' => $provider->id,
            'user_id' => $user->id,
            'title' => $data['title'] ?? null,
            'bio_ar' => $data['bio_ar'] ?? null,
            'bio_en' => $data['bio_en'] ?? null,
            'consultation_fee' => $data['consultation_fee'] ?? null,
            'years_of_experience' => $data['years_of_experience'] ?? null,
        ]);

        if (! empty($data['specialty_ids'])) {
            $profile->specialties()->sync($data['specialty_ids']);
        }

        return $profile;
    }

    private function roleForProviderType(ProviderType $type): UserRole
    {
        return match ($type) {
            ProviderType::Doctor => UserRole::Doctor,
            ProviderType::Pharmacy => UserRole::PharmacyAdmin,
            ProviderType::Lab => UserRole::LabAdmin,
            default => UserRole::ProviderAdmin,
        };
    }

    private function createTypeProfile(Provider $provider, User $user, array $data): object
    {
        $profileData = $data['profile'] ?? [];

        return match ($provider->type) {
            ProviderType::Doctor => $this->createDoctorProfile($provider, $user, array_merge($profileData, $data)),
            ProviderType::Pharmacy => PharmacyProfile::query()->create([
                'provider_id' => $provider->id,
                'license_number' => $profileData['license_number'] ?? $data['license_number'] ?? null,
                'delivery_available' => (bool) ($profileData['delivery_available'] ?? $data['delivery_available'] ?? false),
            ]),
            ProviderType::Lab => LabProfile::query()->create([
                'provider_id' => $provider->id,
                'license_number' => $profileData['license_number'] ?? $data['license_number'] ?? null,
                'home_collection_available' => (bool) ($profileData['home_collection_available'] ?? $data['home_collection_available'] ?? false),
            ]),
            ProviderType::Hospital => HospitalProfile::query()->create($this->profilePayload($provider, $profileData, [
                'license_number',
                'description_ar',
                'description_en',
                'emergency_available',
                'has_inpatient',
                'has_outpatient',
                'has_icu',
                'has_ambulance',
            ])),
            ProviderType::Clinic => ClinicProfile::query()->create($this->profilePayload($provider, $profileData, [
                'clinic_type',
                'description_ar',
                'description_en',
            ])),
            ProviderType::MedicalCenter => MedicalCenterProfile::query()->create($this->profilePayload($provider, $profileData, [
                'center_type',
                'description_ar',
                'description_en',
            ])),
            ProviderType::Radiology => RadiologyProfile::query()->create($this->profilePayload($provider, $profileData, [
                'license_number',
                'home_service_enabled',
                'report_delivery_enabled',
                'dicom_supported',
                'description_ar',
                'description_en',
            ])),
            ProviderType::Gym => GymProfile::query()->create($this->profilePayload($provider, $profileData, [
                'men_allowed',
                'women_allowed',
                'ladies_only_hours',
                'has_classes',
                'has_personal_training',
                'description_ar',
                'description_en',
            ])),
            ProviderType::FitnessCoach, ProviderType::NutritionCoach => CoachProfile::query()->create($this->profilePayload($provider, $profileData + [
                'coach_type' => $provider->type === ProviderType::NutritionCoach ? CoachType::Nutrition->value : CoachType::Fitness->value,
            ], [
                'coach_type',
                'experience_years',
                'session_price',
                'monthly_followup_price',
                'online_coaching_enabled',
                'gym_visit_enabled',
                'home_training_enabled',
                'certifications_summary',
            ])),
            ProviderType::Physiotherapy => PhysiotherapyProfile::query()->create($this->profilePayload($provider, $profileData, [
                'home_visit_enabled',
                'center_visit_enabled',
                'session_price',
                'description_ar',
                'description_en',
            ])),
            ProviderType::HomeHealthcare => HomeHealthcareProfile::query()->create($this->profilePayload($provider, $profileData, [
                'nursing_enabled',
                'injections_enabled',
                'wound_care_enabled',
                'elderly_care_enabled',
                'physiotherapy_home_enabled',
                'service_radius_km',
                'description_ar',
                'description_en',
            ])),
        };
    }

    private function profilePayload(Provider $provider, array $profileData, array $allowedKeys): array
    {
        return ['provider_id' => $provider->id]
            + collect($profileData)->only($allowedKeys)->all();
    }
}
