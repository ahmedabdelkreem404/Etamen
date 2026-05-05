<?php

namespace App\Modules\Health\Application\Services;

use App\Models\User;
use App\Modules\Health\Infrastructure\Models\HealthAccessLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class HealthAccessLogService
{
    public function logRead(int $patientUserId, ?User $actor, string $action, ?Model $target = null, array $metadata = [], ?Request $request = null): HealthAccessLog
    {
        return HealthAccessLog::query()->create([
            'patient_user_id' => $patientUserId,
            'actor_id' => $actor?->id,
            'action' => $action,
            'target_type' => $target ? $target::class : null,
            'target_id' => $target?->getKey(),
            'metadata' => $metadata,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'created_at' => now(),
        ]);
    }
}
