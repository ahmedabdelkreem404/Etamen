<?php

namespace App\Modules\Appointments\Application\Services;

use App\Models\User;
use App\Modules\Appointments\Domain\Enums\AppointmentSlotStatus;
use App\Modules\Appointments\Infrastructure\Models\AppointmentSlot;
use App\Modules\Appointments\Infrastructure\Models\DoctorHoliday;
use App\Modules\Appointments\Infrastructure\Models\DoctorSchedule;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Providers\Infrastructure\Models\DoctorProfile;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class GenerateDoctorSlots
{
    public function __construct(
        private readonly DoctorBookingContextService $contextService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function generate(User $user, array $data): array
    {
        $doctor = $this->contextService->doctorForUser($user, $data['doctor_profile_id'] ?? null);
        [$startDate, $endDate, $requestedEndDate] = $this->resolveDateRange($data);

        return DB::transaction(function () use ($doctor, $startDate, $endDate, $requestedEndDate, $user): array {
            $created = 0;
            $skipped = 0;

            $schedules = $doctor->schedules()
                ->where('is_active', true)
                ->with(['days' => fn ($query) => $query->where('is_active', true)])
                ->get();

            foreach ($schedules as $schedule) {
                $scheduleEndDate = $this->scheduleEndDate($schedule, $startDate, $endDate);

                for ($date = $startDate; $date->lessThanOrEqualTo($scheduleEndDate); $date = $date->addDay()) {
                    foreach ($schedule->days->where('day_of_week', $date->dayOfWeek) as $day) {
                        [$dayCreated, $daySkipped] = $this->generateForDay($doctor, $schedule, $day, $date);
                        $created += $dayCreated;
                        $skipped += $daySkipped;
                    }
                }
            }

            $this->auditLogService->log('appointment_slots.generated', $doctor, $user, metadata: [
                'created' => $created,
                'skipped' => $skipped,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'requested_end_date' => $requestedEndDate->toDateString(),
            ]);

            return [
                'created' => $created,
                'skipped' => $skipped,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ];
        });
    }

    private function resolveDateRange(array $data): array
    {
        $daysAhead = min((int) ($data['days_ahead'] ?? 14), 60);
        $startDate = isset($data['start_date'])
            ? CarbonImmutable::parse($data['start_date'])->startOfDay()
            : now()->toImmutable()->startOfDay();

        $requestedEndDate = isset($data['end_date'])
            ? CarbonImmutable::parse($data['end_date'])->endOfDay()
            : $startDate->addDays($daysAhead)->endOfDay();

        $maxEndDate = $startDate->addDays(60)->endOfDay();
        $endDate = $requestedEndDate->lessThanOrEqualTo($maxEndDate)
            ? $requestedEndDate
            : $maxEndDate;

        return [$startDate, $endDate, $requestedEndDate];
    }

    private function scheduleEndDate(DoctorSchedule $schedule, CarbonImmutable $startDate, CarbonImmutable $requestedEndDate): CarbonImmutable
    {
        $scheduleMaxDaysAhead = min(max((int) $schedule->max_days_ahead, 0), 60);
        $scheduleEndDate = $startDate->addDays($scheduleMaxDaysAhead)->endOfDay();

        return $scheduleEndDate->lessThanOrEqualTo($requestedEndDate)
            ? $scheduleEndDate
            : $requestedEndDate;
    }

    private function generateForDay(DoctorProfile $doctor, DoctorSchedule $schedule, $day, CarbonImmutable $date): array
    {
        $created = 0;
        $skipped = 0;
        $cursor = $date->setTimeFromTimeString((string) $day->start_time);
        $end = $date->setTimeFromTimeString((string) $day->end_time);
        $duration = $schedule->slot_duration_minutes;
        $step = $duration + $schedule->buffer_minutes;

        while ($cursor->addMinutes($duration)->lessThanOrEqualTo($end)) {
            $slotEnd = $cursor->addMinutes($duration);

            if ($cursor->lessThanOrEqualTo(now()) || $this->overlapsHoliday($doctor, $cursor, $slotEnd)) {
                $skipped++;
                $cursor = $cursor->addMinutes($step);

                continue;
            }

            $inserted = AppointmentSlot::query()->insertOrIgnore([
                'doctor_profile_id' => $doctor->id,
                'provider_id' => $doctor->provider_id,
                'branch_id' => $schedule->branch_id,
                'starts_at' => $cursor,
                'ends_at' => $slotEnd,
                'status' => AppointmentSlotStatus::Available->value,
                'generated_from_schedule_id' => $schedule->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($inserted === 1) {
                $created++;
            } else {
                $skipped++;
            }

            $cursor = $cursor->addMinutes($step);
        }

        return [$created, $skipped];
    }

    private function overlapsHoliday(DoctorProfile $doctor, CarbonImmutable $startsAt, CarbonImmutable $endsAt): bool
    {
        return DoctorHoliday::query()
            ->where('doctor_profile_id', $doctor->id)
            ->where('is_active', true)
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();
    }
}
