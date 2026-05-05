<?php

namespace App\Modules\Labs\Policies;

use App\Models\User;
use App\Modules\Labs\Domain\Enums\LabResultStatus;
use App\Modules\Labs\Infrastructure\Models\LabResult;
use App\Modules\Providers\Infrastructure\Models\Provider;

class LabResultPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }

    public function view(User $user, LabResult $result): bool
    {
        return $this->canAccessResult($user, $result);
    }

    public function download(User $user, LabResult $result): bool
    {
        return $result->status === LabResultStatus::VisibleToPatient
            && $this->canAccessResult($user, $result);
    }

    private function canAccessResult(User $user, LabResult $result): bool
    {
        $order = $result->order;

        if ((int) $order->patient_user_id === (int) $user->id) {
            return true;
        }

        $provider = Provider::query()->find($order->lab_provider_id);

        return $provider && $user->ownsProvider($provider);
    }
}
