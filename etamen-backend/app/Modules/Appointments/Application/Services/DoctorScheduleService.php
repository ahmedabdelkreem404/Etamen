<?php

namespace App\Modules\Appointments\Application\Services;

use App\Models\User;
use App\Modules\Appointments\Infrastructure\Models\DoctorHoliday;
use App\Modules\Appointments\Infrastructure\Models\DoctorSchedule;
use App\Modules\Appointments\Infrastructure\Models\DoctorScheduleDay;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Providers\Infrastructure\Models\ProviderBranch;
use Illuminate\Validation\ValidationException;

class DoctorScheduleService
{
    public function __construct(
        private readonly DoctorBookingContextService $contextService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function createSchedule(User $user, array $data): DoctorSchedule
    {
        $doctor = $this->contextService->doctorForUser($user, $data['doctor_profile_id'] ?? null);
        $this->assertBranchBelongsToProvider($data['branch_id'] ?? null, $doctor->provider_id);

        $schedule = DoctorSchedule::query()->create([
            'doctor_profile_id' => $doctor->id,
            'provider_id' => $doctor->provider_id,
            'branch_id' => $data['branch_id'] ?? null,
            'name' => $data['name'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'slot_duration_minutes' => $data['slot_duration_minutes'] ?? 30,
            'buffer_minutes' => $data['buffer_minutes'] ?? 0,
            'max_days_ahead' => $data['max_days_ahead'] ?? 14,
        ]);

        $this->auditLogService->log('doctor_schedule.created', $schedule, $user);

        return $schedule;
    }

    public function updateSchedule(User $user, DoctorSchedule $schedule, array $data): DoctorSchedule
    {
        $this->assertBranchBelongsToProvider($data['branch_id'] ?? null, $schedule->provider_id);

        $before = $schedule->getAttributes();
        $schedule->update(collect($data)->except('doctor_profile_id')->all());
        $this->auditLogService->log('doctor_schedule.updated', $schedule, $user, before: $before, after: $schedule->getAttributes());

        return $schedule->refresh();
    }

    public function addScheduleDay(User $user, DoctorSchedule $schedule, array $data): DoctorScheduleDay
    {
        $day = $schedule->days()->create($data);
        $this->auditLogService->log('doctor_schedule_day.created', $day, $user, metadata: ['schedule_id' => $schedule->id]);

        return $day;
    }

    public function updateScheduleDay(User $user, DoctorScheduleDay $day, array $data): DoctorScheduleDay
    {
        $before = $day->getAttributes();
        $day->update($data);
        $this->auditLogService->log('doctor_schedule_day.updated', $day, $user, before: $before, after: $day->getAttributes());

        return $day->refresh();
    }

    public function createHoliday(User $user, array $data): DoctorHoliday
    {
        $doctor = $this->contextService->doctorForUser($user, $data['doctor_profile_id'] ?? null);

        $holiday = DoctorHoliday::query()->create([
            'doctor_profile_id' => $doctor->id,
            'provider_id' => $doctor->provider_id,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'reason' => $data['reason'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        $this->auditLogService->log('doctor_holiday.created', $holiday, $user);

        return $holiday;
    }

    private function assertBranchBelongsToProvider(?int $branchId, int $providerId): void
    {
        if (! $branchId) {
            return;
        }

        $belongs = ProviderBranch::query()
            ->whereKey($branchId)
            ->where('provider_id', $providerId)
            ->exists();

        if (! $belongs) {
            throw ValidationException::withMessages([
                'branch_id' => ['The selected branch does not belong to this provider.'],
            ]);
        }
    }
}
