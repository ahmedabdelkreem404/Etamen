<?php

namespace App\Modules\CarePlans\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\CarePlans\Domain\Enums\CarePlanStatus;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanCheckin;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CarePlanCheckinService
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function record(User $patient, CarePlan $plan, array $data): CarePlanCheckin
    {
        return DB::transaction(function () use ($patient, $plan, $data): CarePlanCheckin {
            $this->assertPlanAcceptsTracking($plan);
            $date = CarbonImmutable::parse($data['checkin_date']);
            $this->assertWithinDateRange($plan, $date);

            $attributes = [
                'care_plan_id' => $plan->id,
                'patient_user_id' => $patient->id,
            ];

            $checkin = CarePlanCheckin::query()
                ->where($attributes)
                ->whereDate('checkin_date', $date->toDateString())
                ->first();
            $before = $checkin?->getAttributes();
            $payload = [
                ...$attributes,
                'checkin_date' => $date->toDateString(),
                'commitment_score' => $data['commitment_score'] ?? null,
                'energy_level' => $data['energy_level'] ?? null,
                'hunger_level' => $data['hunger_level'] ?? null,
                'sleep_quality' => $data['sleep_quality'] ?? null,
                'mood' => $data['mood'] ?? null,
                'symptoms_notes' => $data['symptoms_notes'] ?? null,
                'general_notes' => $data['general_notes'] ?? null,
            ];

            if ($checkin) {
                $checkin->fill($payload)->save();
            } else {
                $checkin = CarePlanCheckin::query()->create($payload);
            }

            $this->auditLogService->log(
                $before ? 'care_plan_checkin.updated' : 'care_plan_checkin.created',
                $checkin,
                $patient,
                before: $before,
                after: $checkin->getAttributes(),
            );

            return $checkin->refresh();
        });
    }

    public function update(User $patient, CarePlanCheckin $checkin, array $data): CarePlanCheckin
    {
        return DB::transaction(function () use ($patient, $checkin, $data): CarePlanCheckin {
            $plan = $checkin->plan;
            $this->assertPlanAcceptsTracking($plan);
            $date = CarbonImmutable::parse($data['checkin_date']);
            $this->assertWithinDateRange($plan, $date);

            $before = $checkin->getAttributes();
            $checkin->fill([
                'checkin_date' => $date->toDateString(),
                'commitment_score' => $data['commitment_score'] ?? null,
                'energy_level' => $data['energy_level'] ?? null,
                'hunger_level' => $data['hunger_level'] ?? null,
                'sleep_quality' => $data['sleep_quality'] ?? null,
                'mood' => $data['mood'] ?? null,
                'symptoms_notes' => $data['symptoms_notes'] ?? null,
                'general_notes' => $data['general_notes'] ?? null,
            ])->save();

            $this->auditLogService->log('care_plan_checkin.updated', $checkin, $patient, before: $before, after: $checkin->getAttributes());

            return $checkin->refresh();
        });
    }

    private function assertPlanAcceptsTracking(CarePlan $plan): void
    {
        if ($plan->status !== CarePlanStatus::Active) {
            throw ValidationException::withMessages([
                'care_plan' => ['This care plan is not active for check-ins.'],
            ]);
        }
    }

    private function assertWithinDateRange(CarePlan $plan, CarbonImmutable $date): void
    {
        if ($date->lt(CarbonImmutable::parse($plan->start_date)->startOfDay())) {
            throw ValidationException::withMessages(['checkin_date' => ['Check-in date is before plan start date.']]);
        }

        if ($plan->end_date && $date->gt(CarbonImmutable::parse($plan->end_date)->endOfDay())) {
            throw ValidationException::withMessages(['checkin_date' => ['Check-in date is after plan end date.']]);
        }
    }
}
