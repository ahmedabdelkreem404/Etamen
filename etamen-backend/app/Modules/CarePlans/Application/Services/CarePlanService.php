<?php

namespace App\Modules\CarePlans\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\CarePlans\Domain\Enums\CarePlanSource;
use App\Modules\CarePlans\Domain\Enums\CarePlanStatus;
use App\Modules\CarePlans\Domain\Enums\CarePlanVisibility;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Support\Facades\DB;

class CarePlanService
{
    public function __construct(
        private readonly CarePlanAccessService $accessService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function createPatientPlan(User $patient, array $data): CarePlan
    {
        return DB::transaction(function () use ($patient, $data): CarePlan {
            $plan = CarePlan::query()->create([
                ...$this->cleanPayload($data),
                'patient_user_id' => $patient->id,
                'status' => CarePlanStatus::Draft,
                'visibility' => CarePlanVisibility::PatientOnly,
                'source' => CarePlanSource::PatientCreated,
                'safety_disclaimer' => CarePlan::SAFETY_DISCLAIMER,
            ]);

            $this->auditLogService->log('care_plan.created', $plan, $patient);

            return $plan->refresh();
        });
    }

    public function update(User $actor, CarePlan $plan, array $data): CarePlan
    {
        return DB::transaction(function () use ($actor, $plan, $data): CarePlan {
            $before = $plan->getAttributes();
            $plan->fill($this->cleanPayload($data))->save();
            $this->auditLogService->log('care_plan.updated', $plan, $actor, before: $before, after: $plan->getAttributes());

            return $plan->refresh();
        });
    }

    public function delete(User $actor, CarePlan $plan): void
    {
        DB::transaction(function () use ($actor, $plan): void {
            $before = $plan->getAttributes();

            if ($plan->checkins()->exists() || $plan->mealLogs()->exists()) {
                $plan->forceFill(['status' => CarePlanStatus::Cancelled])->save();
                $this->auditLogService->log('care_plan.cancelled_on_delete', $plan, $actor, before: $before, after: $plan->getAttributes());

                return;
            }

            $plan->delete();
            $this->auditLogService->log('care_plan.deleted', $plan, $actor, before: $before, after: []);
        });
    }

    public function assignByProvider(User $providerUser, array $data): CarePlan
    {
        return DB::transaction(function () use ($providerUser, $data): CarePlan {
            $provider = $this->accessService->currentDoctorProviderFor($providerUser);
            $this->accessService->assertProviderCanAssignToPatient($provider, (int) $data['patient_user_id']);

            $plan = $this->createAssignedPlan(
                actor: $providerUser,
                patientUserId: (int) $data['patient_user_id'],
                provider: $provider,
                data: $data,
                source: CarePlanSource::ProviderAssigned,
                visibility: CarePlanVisibility::ProviderAssigned,
                auditAction: 'care_plan.provider_assigned',
            );

            return $plan;
        });
    }

    public function createByAdmin(User $admin, array $data): CarePlan
    {
        return DB::transaction(function () use ($admin, $data): CarePlan {
            $provider = ! empty($data['provider_id']) ? Provider::query()->findOrFail($data['provider_id']) : null;

            return $this->createAssignedPlan(
                actor: $admin,
                patientUserId: (int) $data['patient_user_id'],
                provider: $provider,
                data: $data,
                source: CarePlanSource::AdminCreated,
                visibility: CarePlanVisibility::AdminManaged,
                auditAction: 'care_plan.admin_created',
            );
        });
    }

    private function createAssignedPlan(
        User $actor,
        int $patientUserId,
        ?Provider $provider,
        array $data,
        CarePlanSource $source,
        CarePlanVisibility $visibility,
        string $auditAction,
    ): CarePlan {
        $plan = CarePlan::query()->create([
            ...$this->cleanPayload($data),
            'patient_user_id' => $patientUserId,
            'assigned_by_user_id' => $actor->id,
            'provider_id' => $provider?->id,
            'status' => CarePlanStatus::Draft,
            'visibility' => $visibility,
            'source' => $source,
            'safety_disclaimer' => CarePlan::SAFETY_DISCLAIMER,
        ]);

        $this->auditLogService->log($auditAction, $plan, $actor, metadata: [
            'provider_id' => $provider?->id,
            'patient_user_id' => $patientUserId,
        ]);

        return $plan->refresh();
    }

    private function cleanPayload(array $data): array
    {
        unset(
            $data['patient_user_id'],
            $data['assigned_by_user_id'],
            $data['provider_id'],
            $data['source'],
            $data['visibility'],
            $data['status'],
            $data['safety_disclaimer'],
        );

        return $data;
    }
}
