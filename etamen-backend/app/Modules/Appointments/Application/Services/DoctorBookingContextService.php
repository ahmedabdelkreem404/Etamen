<?php

namespace App\Modules\Appointments\Application\Services;

use App\Models\User;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\DoctorProfile;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DoctorBookingContextService
{
    public function doctorForUser(User $user, ?int $doctorProfileId = null): DoctorProfile
    {
        if ($user->isPlatformAdmin() && $doctorProfileId) {
            return DoctorProfile::query()->with('provider')->findOrFail($doctorProfileId);
        }

        $provider = $user->ownedProviders()
            ->where('type', ProviderType::Doctor)
            ->with('doctorProfile')
            ->first();

        if (! $provider?->doctorProfile) {
            throw (new ModelNotFoundException)->setModel(Provider::class);
        }

        if ($doctorProfileId && $provider->doctorProfile->id !== $doctorProfileId) {
            throw new AuthorizationException('You cannot manage this doctor profile.');
        }

        return $provider->doctorProfile;
    }
}
