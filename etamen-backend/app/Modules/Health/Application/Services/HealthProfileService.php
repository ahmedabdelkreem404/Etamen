<?php

namespace App\Modules\Health\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Health\Infrastructure\Models\HealthProfile;
use Illuminate\Support\Facades\DB;

class HealthProfileService
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function profileFor(User $patient): HealthProfile
    {
        return HealthProfile::query()->firstOrCreate(['patient_user_id' => $patient->id]);
    }

    public function update(User $patient, array $data): HealthProfile
    {
        return DB::transaction(function () use ($patient, $data): HealthProfile {
            $profile = $this->profileFor($patient);
            $before = $profile->getAttributes();
            $profile->fill($data)->save();

            $this->auditLogService->log('health_profile.updated', $profile, $patient, before: $before, after: $profile->getAttributes());

            return $profile->refresh();
        });
    }
}
