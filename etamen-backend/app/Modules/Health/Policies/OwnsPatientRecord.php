<?php

namespace App\Modules\Health\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait OwnsPatientRecord
{
    public function view(User $user, Model $record): bool
    {
        return $user->isPlatformAdmin() || (int) $record->patient_user_id === (int) $user->id;
    }

    public function update(User $user, Model $record): bool
    {
        return (int) $record->patient_user_id === (int) $user->id;
    }

    public function delete(User $user, Model $record): bool
    {
        return (int) $record->patient_user_id === (int) $user->id;
    }
}
