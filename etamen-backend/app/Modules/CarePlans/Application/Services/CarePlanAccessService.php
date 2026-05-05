<?php

namespace App\Modules\CarePlans\Application\Services;

use App\Models\User;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class CarePlanAccessService
{
    public function currentDoctorProviderFor(User $user): Provider
    {
        $provider = $user->ownedProviders()
            ->where('type', ProviderType::Doctor)
            ->first();

        if (! $provider) {
            throw (new ModelNotFoundException)->setModel(Provider::class);
        }

        $this->assertApprovedDoctor($provider);

        return $provider;
    }

    public function assertApprovedDoctor(Provider $provider): void
    {
        if ($provider->type !== ProviderType::Doctor || $provider->status !== ProviderStatus::Approved || ! $provider->is_active) {
            throw ValidationException::withMessages([
                'provider_id' => ['The selected doctor provider is not available for care plan assignment.'],
            ]);
        }
    }

    public function assertProviderCanAssignToPatient(Provider $provider, int $patientUserId): void
    {
        $exists = Appointment::query()
            ->where('provider_id', $provider->id)
            ->where('patient_user_id', $patientUserId)
            ->whereIn('status', [
                AppointmentStatus::Confirmed->value,
                AppointmentStatus::Accepted->value,
                AppointmentStatus::Completed->value,
            ])
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'patient_user_id' => ['This patient does not have an eligible appointment relationship with this provider.'],
            ]);
        }
    }
}
