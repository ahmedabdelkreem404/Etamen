<?php

namespace App\Modules\CarePlans\Policies;

use App\Models\User;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;

trait CarePlanChildPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPlatformAdmin() ? true : null;
    }

    public function view(User $user, mixed $record): bool
    {
        return app(CarePlanPolicy::class)->view($user, $this->planFor($record));
    }

    public function update(User $user, mixed $record): bool
    {
        return app(CarePlanPolicy::class)->manageStructure($user, $this->planFor($record));
    }

    public function delete(User $user, mixed $record): bool
    {
        return app(CarePlanPolicy::class)->manageStructure($user, $this->planFor($record));
    }

    private function planFor(mixed $record): CarePlan
    {
        if (method_exists($record, 'plan')) {
            return $record->plan;
        }

        return $record->day->plan;
    }
}
