<?php

namespace App\Modules\Medications\Application\Services;

use App\Models\User;
use App\Modules\Medications\Domain\Enums\MedicationFrequencyType;
use App\Modules\Medications\Domain\Enums\MedicationReminderStatus;
use App\Modules\Medications\Infrastructure\Models\MedicationReminder;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MedicationScheduleService
{
    public function occurrences(MedicationReminder $reminder, string|CarbonImmutable|null $from = null, string|CarbonImmutable|null $to = null): Collection
    {
        if ($reminder->frequency_type === MedicationFrequencyType::AsNeeded) {
            return collect();
        }

        [$start, $end] = $this->boundedRange($from, $to);
        $reminderStart = CarbonImmutable::parse($reminder->start_date)->startOfDay();
        $reminderEnd = $reminder->end_date ? CarbonImmutable::parse($reminder->end_date)->endOfDay() : null;

        if ($start->lt($reminderStart)) {
            $start = $reminderStart;
        }

        if ($reminderEnd && $end->gt($reminderEnd)) {
            $end = $reminderEnd;
        }

        if ($start->gt($end)) {
            return collect();
        }

        $times = $reminder->activeTimes()->get();
        $days = collect();
        for ($day = $start->startOfDay(); $day->lte($end); $day = $day->addDay()) {
            if (! $this->isScheduledDay($reminder, $day)) {
                continue;
            }

            foreach ($this->timesForDay($reminder, $times) as $time) {
                [$hour, $minute] = $this->hourMinute($time['time_of_day']);
                $scheduledFor = $day->setTime($hour, $minute);

                if ($scheduledFor->betweenIncluded($start, $end)) {
                    $days->push([
                        'reminder_id' => $reminder->id,
                        'medication_name' => $reminder->medication_name,
                        'scheduled_for' => $scheduledFor,
                        'time_id' => $time['id'] ?? null,
                        'label' => $time['label'] ?? null,
                    ]);
                }
            }
        }

        return $days->sortBy('scheduled_for')->values();
    }

    public function today(User $patient): Collection
    {
        $from = now()->toImmutable()->startOfDay();
        $to = now()->toImmutable()->endOfDay();

        return $this->forPatient($patient, $from, $to);
    }

    public function upcoming(User $patient, int $days = 7): Collection
    {
        $days = max(1, min($days, 30));

        return $this->forPatient($patient, now()->toImmutable(), now()->toImmutable()->addDays($days));
    }

    public function forPatient(User $patient, CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        return MedicationReminder::query()
            ->with('times')
            ->where('patient_user_id', $patient->id)
            ->where('status', MedicationReminderStatus::Active->value)
            ->get()
            ->flatMap(fn (MedicationReminder $reminder) => $this->occurrences($reminder, $from, $to))
            ->sortBy('scheduled_for')
            ->values();
    }

    public function boundedRange(string|CarbonImmutable|null $from = null, string|CarbonImmutable|null $to = null): array
    {
        $end = $to ? CarbonImmutable::parse($to)->endOfDay() : now()->toImmutable()->endOfDay();
        $start = $from ? CarbonImmutable::parse($from)->startOfDay() : $end->subDays(30)->startOfDay();

        if ($start->diffInDays($end) > 365) {
            $start = $end->subDays(365)->startOfDay();
        }

        return [$start, $end];
    }

    private function isScheduledDay(MedicationReminder $reminder, CarbonImmutable $day): bool
    {
        if ($reminder->frequency_type !== MedicationFrequencyType::SpecificDays) {
            return true;
        }

        $days = data_get($reminder->metadata, 'days_of_week', []);

        return in_array($day->dayOfWeek, array_map('intval', $days), true);
    }

    private function timesForDay(MedicationReminder $reminder, Collection $times): array
    {
        if ($reminder->frequency_type === MedicationFrequencyType::EveryXHours) {
            $interval = max(1, min((int) $reminder->interval_hours, 24));
            $start = $times->first()?->time_of_day ?? '00:00';
            [$hour, $minute] = $this->hourMinute($start);
            $generated = [];

            for ($currentHour = $hour; $currentHour < 24; $currentHour += $interval) {
                $generated[] = [
                    'id' => $times->first()?->id,
                    'time_of_day' => sprintf('%02d:%02d', $currentHour, $minute),
                    'label' => $times->first()?->label,
                ];
            }

            return $generated;
        }

        return $times
            ->map(fn ($time) => [
                'id' => $time->id,
                'time_of_day' => $time->time_of_day,
                'label' => $time->label,
            ])
            ->all();
    }

    private function hourMinute(string $time): array
    {
        $parts = explode(':', $time);

        return [(int) $parts[0], (int) ($parts[1] ?? 0)];
    }
}
