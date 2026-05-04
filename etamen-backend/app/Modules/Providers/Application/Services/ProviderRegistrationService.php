<?php

namespace App\Modules\Providers\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Providers\Domain\Enums\ApprovalRequestStatus;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\DoctorProfile;
use App\Modules\Providers\Infrastructure\Models\LabProfile;
use App\Modules\Providers\Infrastructure\Models\PharmacyProfile;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderApprovalRequest;
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
                    'city_id' => $data['city_id'] ?? null,
                    'area_id' => $data['area_id'] ?? null,
                    'address_ar' => $data['address_ar'] ?? null,
                    'address_en' => $data['address_en'] ?? null,
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null,
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
}
