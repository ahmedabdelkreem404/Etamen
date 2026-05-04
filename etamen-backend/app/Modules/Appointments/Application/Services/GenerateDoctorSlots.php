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
        $daysAhead = min((int) ($data['days_ahead'] ?? 14), 60);
        $startDate = isset($data['start_date']) ? CarbonImmutable::parse($data['start_date'])->startOfDay() : now()->toImmutable()->startOfDay();
        $endDate = isset($data['end_date']) ? CarbonImmutable::parse($data['end_date'])->endOfDay() : $startDate->addDays($daysAhead)->endOfDay();

        return DB::transaction(function () use ($doctor, $startDate, $endDate, $user): array {
            $created = 0;
            $skipped = 0;

            $schedules = $doctor->schedules()
                ->where('is_active', true)
                ->with(['days' => fn ($query) => $query->where('is_active', true)])
                ->get();

            for ($date = $startDate; $date->lessThanOrEqualTo($endDate); $date = $date->addDay()) {
                foreach ($schedules as $schedule) {
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
            ]);

            return ['created' => $created, 'skipped' => $skipped];
        });
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

            $exists = AppointmentSlot::query()
                ->where('doctor_profile_id', $doctor->id)
                ->where('starts_at', $cursor)
                ->where('ends_at', $slotEnd)
                ->exists();

            if (! $exists) {
                AppointmentSlot::query()->create([
                    'doctor_profile_id' => $doctor->id,
                    'provider_id' => $doctor->provider_id,
                    'branch_id' => $schedule->branch_id,
                    'starts_at' => $cursor,
                    'ends_at' => $slotEnd,
                    'status' => AppointmentSlotStatus::Available,
                    'generated_from_schedule_id' => $schedule->id,
                ]);
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
