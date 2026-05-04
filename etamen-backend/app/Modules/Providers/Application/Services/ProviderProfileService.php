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
            ->with(['doctorProfile.specialties', 'pharmacyProfile', 'labProfile', 'branches'])
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
            };

            $this->auditLogService->log('provider.profile_updated', $provider, $user, before: $before, after: $provider->getAttributes());

            return $provider->refresh()->load(['doctorProfile.specialties', 'pharmacyProfile', 'labProfile', 'branches']);
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
}
