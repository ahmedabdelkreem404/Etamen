<?php

namespace App\Modules\Medications\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait OwnsMedicationRecord
{
    public function viewAny(User $user): bool
    {
        return $user->isPlatformAdmin();
    }

    public function view(User $user, Model $record): bool
    {
        return $user->isPlatformAdmin() || (int) $this->patientUserId($record) === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('patient');
    }

    public function update(User $user, Model $record): bool
    {
        return (int) $this->patientUserId($record) === (int) $user->id;
    }

    public function delete(User $user, Model $record): bool
    {
        return (int) $this->patientUserId($record) === (int) $user->id;
    }

    private function patientUserId(Model $record): ?int
    {
        if (isset($record->patient_user_id)) {
            return (int) $record->patient_user_id;
        }

        if (method_exists($record, 'reminder')) {
            return (int) $record->reminder?->patient_user_id;
        }

        return null;
    }
}
