<?php

namespace App\Modules\CarePlans\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\CarePlans\Domain\Enums\CarePlanStatus;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanMeal;
use App\Modules\CarePlans\Infrastructure\Models\MealLog;
use App\Modules\MedicalFiles\Application\Services\FileStorageService;
use App\Modules\MedicalFiles\Domain\Enums\FileCategory;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MealLogService
{
    public function __construct(
        private readonly FileStorageService $fileStorageService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function create(User $patient, CarePlan $plan, array $data, ?UploadedFile $photo = null): MealLog
    {
        return DB::transaction(function () use ($patient, $plan, $data, $photo): MealLog {
            $this->assertPlanAcceptsTracking($plan);
            $this->assertMealBelongsToPlan($plan, $data['care_plan_meal_id'] ?? null);
            $loggedAt = CarbonImmutable::parse($data['logged_at']);
            $this->assertWithinDateRange($plan, $loggedAt);

            $log = MealLog::query()->create([
                'care_plan_id' => $plan->id,
                'care_plan_meal_id' => $data['care_plan_meal_id'] ?? null,
                'patient_user_id' => $patient->id,
                'logged_at' => $loggedAt,
                'meal_type' => $data['meal_type'] ?? null,
                'status' => $data['status'],
                'description' => $data['description'] ?? null,
                'notes' => $data['notes'] ?? null,
                'metadata' => $data['metadata'] ?? null,
            ]);

            if ($photo) {
                $uploaded = $this->fileStorageService->storePrivate(
                    $photo,
                    FileCategory::MealPhoto,
                    $patient,
                    $log,
                    ['care_plan_id' => $plan->id],
                );
                $log->forceFill(['photo_file_id' => $uploaded->id])->save();
            }

            $this->auditLogService->log('meal_log.created', $log, $patient, metadata: ['care_plan_id' => $plan->id]);

            return $log->refresh()->load('photo');
        });
    }

    public function update(User $patient, MealLog $log, array $data, ?UploadedFile $photo = null): MealLog
    {
        return DB::transaction(function () use ($patient, $log, $data, $photo): MealLog {
            $plan = $log->plan;
            $this->assertPlanAcceptsTracking($plan);
            $this->assertMealBelongsToPlan($plan, $data['care_plan_meal_id'] ?? null);
            $loggedAt = CarbonImmutable::parse($data['logged_at']);
            $this->assertWithinDateRange($plan, $loggedAt);

            $before = $log->getAttributes();
            $log->fill([
                'care_plan_meal_id' => $data['care_plan_meal_id'] ?? null,
                'logged_at' => $loggedAt,
                'meal_type' => $data['meal_type'] ?? null,
                'status' => $data['status'],
                'description' => $data['description'] ?? null,
                'notes' => $data['notes'] ?? null,
                'metadata' => $data['metadata'] ?? null,
            ])->save();

            if ($photo) {
                $uploaded = $this->fileStorageService->storePrivate(
                    $photo,
                    FileCategory::MealPhoto,
                    $patient,
                    $log,
                    ['care_plan_id' => $plan->id],
                );
                $log->forceFill(['photo_file_id' => $uploaded->id])->save();
            }

            $this->auditLogService->log('meal_log.updated', $log, $patient, before: $before, after: $log->getAttributes());

            return $log->refresh()->load('photo');
        });
    }

    public function delete(User $patient, MealLog $log): void
    {
        DB::transaction(function () use ($patient, $log): void {
            $before = $log->getAttributes();
            $log->delete();
            $this->auditLogService->log('meal_log.deleted', $log, $patient, before: $before, after: []);
        });
    }

    private function assertPlanAcceptsTracking(CarePlan $plan): void
    {
        if ($plan->status !== CarePlanStatus::Active) {
            throw ValidationException::withMessages([
                'care_plan' => ['This care plan is not active for meal logs.'],
            ]);
        }
    }

    private function assertMealBelongsToPlan(CarePlan $plan, mixed $mealId): void
    {
        if (! $mealId) {
            return;
        }

        $belongs = CarePlanMeal::query()
            ->whereKey($mealId)
            ->whereHas('day', fn ($query) => $query->where('care_plan_id', $plan->id))
            ->exists();

        if (! $belongs) {
            throw ValidationException::withMessages([
                'care_plan_meal_id' => ['The selected meal does not belong to this care plan.'],
            ]);
        }
    }

    private function assertWithinDateRange(CarePlan $plan, CarbonImmutable $loggedAt): void
    {
        if ($loggedAt->lt(CarbonImmutable::parse($plan->start_date)->startOfDay())) {
            throw ValidationException::withMessages(['logged_at' => ['Meal log is before plan start date.']]);
        }

        if ($plan->end_date && $loggedAt->gt(CarbonImmutable::parse($plan->end_date)->endOfDay())) {
            throw ValidationException::withMessages(['logged_at' => ['Meal log is after plan end date.']]);
        }
    }
}
