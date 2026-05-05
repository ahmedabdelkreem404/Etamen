<?php

namespace App\Modules\Medications\Application\Services;

use App\Models\User;
use App\Modules\Medications\Domain\Enums\MedicationFrequencyType;
use App\Modules\Medications\Domain\Enums\MedicationLogAction;
use App\Modules\Medications\Infrastructure\Models\MedicationLog;
use App\Modules\Medications\Infrastructure\Models\MedicationReminder;
use Carbon\CarbonImmutable;

class MedicationAdherenceService
{
    public const SUMMARY_DISCLAIMER = 'هذا الملخص يساعدك على متابعة الالتزام فقط، ولا يعني أن الدواء مناسب أو غير مناسب. لا تغيّر الجرعة أو توقف الدواء بدون الرجوع للطبيب.';

    public function __construct(private readonly MedicationScheduleService $scheduleService) {}

    public function summary(User $patient, array $filters = []): array
    {
        [$from, $to] = $this->range($filters['from'] ?? null, $filters['to'] ?? null);
        $reminders = MedicationReminder::query()
            ->with('times')
            ->where('patient_user_id', $patient->id)
            ->get();

        $byReminder = $reminders
            ->reject(fn (MedicationReminder $reminder) => $reminder->frequency_type === MedicationFrequencyType::AsNeeded)
            ->map(fn (MedicationReminder $reminder) => $this->reminderSummary($reminder, $from, $to))
            ->values();

        $totalScheduled = (int) $byReminder->sum('total_scheduled');
        $taken = (int) $byReminder->sum('taken_count');
        $skipped = (int) $byReminder->sum('skipped_count');
        $missed = (int) $byReminder->sum('missed_count');

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'total_scheduled' => $totalScheduled,
            'taken_count' => $taken,
            'skipped_count' => $skipped,
            'missed_count' => $missed,
            'adherence_percentage' => $totalScheduled > 0 ? round(($taken / $totalScheduled) * 100, 2) : null,
            'as_needed_count' => $reminders->where('frequency_type', MedicationFrequencyType::AsNeeded)->count(),
            'by_reminder' => $byReminder->all(),
            'disclaimer' => self::SUMMARY_DISCLAIMER,
        ];
    }

    private function reminderSummary(MedicationReminder $reminder, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $scheduled = $this->scheduleService->occurrences($reminder, $from, $to);
        $dueScheduled = $scheduled->filter(fn (array $item) => $item['scheduled_for']->lte(now()));
        $scheduledKeys = $dueScheduled
            ->map(fn (array $item) => $item['scheduled_for']->toDateTimeString())
            ->all();

        $logs = MedicationLog::query()
            ->where('medication_reminder_id', $reminder->id)
            ->whereBetween('scheduled_for', [$from, $to])
            ->get();

        $taken = $logs->where('action', MedicationLogAction::Taken)->count();
        $skipped = $logs->where('action', MedicationLogAction::Skipped)->count();
        $loggedMissed = $logs->where('action', MedicationLogAction::Missed)->count();
        $scheduledWithoutTakenOrSkipped = max(count($scheduledKeys) - $taken - $skipped, 0);
        $missed = max($loggedMissed, $scheduledWithoutTakenOrSkipped);

        return [
            'reminder_id' => $reminder->id,
            'medication_name' => $reminder->medication_name,
            'total_scheduled' => count($scheduledKeys),
            'taken_count' => $taken,
            'skipped_count' => $skipped,
            'missed_count' => $missed,
            'adherence_percentage' => count($scheduledKeys) > 0 ? round(($taken / count($scheduledKeys)) * 100, 2) : null,
        ];
    }

    private function range(?string $from, ?string $to): array
    {
        $end = $to ? CarbonImmutable::parse($to)->endOfDay() : now()->toImmutable()->endOfDay();
        $start = $from ? CarbonImmutable::parse($from)->startOfDay() : $end->subDays(30)->startOfDay();

        if ($start->diffInDays($end) > 365) {
            $start = $end->subDays(365)->startOfDay();
        }

        return [$start, $end];
    }
}
