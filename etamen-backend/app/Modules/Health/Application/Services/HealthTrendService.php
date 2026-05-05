<?php

namespace App\Modules\Health\Application\Services;

use App\Models\User;
use App\Modules\Health\Domain\Enums\VitalType;
use App\Modules\Health\Infrastructure\Models\VitalRecord;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class HealthTrendService
{
    public function trends(User $patient, array $filters): array
    {
        $type = VitalType::from($filters['vital_type']);
        [$from, $to] = $this->range($filters['from'] ?? null, $filters['to'] ?? null);
        $groupBy = $filters['group_by'] ?? 'day';

        $records = VitalRecord::query()
            ->where('patient_user_id', $patient->id)
            ->where('vital_type', $type)
            ->whereBetween('measured_at', [$from, $to])
            ->orderBy('measured_at')
            ->get();

        $points = $records
            ->groupBy(fn (VitalRecord $record) => $this->groupKey($record, $groupBy))
            ->map(fn (Collection $group, string $key) => [
                'date' => $key,
                'average_value' => round((float) $group->avg('value_decimal'), 2),
                'average_secondary_value' => $group->whereNotNull('value_secondary_decimal')->isNotEmpty()
                    ? round((float) $group->avg('value_secondary_decimal'), 2)
                    : null,
                'min' => round((float) $group->min('value_decimal'), 2),
                'max' => round((float) $group->max('value_decimal'), 2),
                'count' => $group->count(),
            ])
            ->values()
            ->all();

        $latest = VitalRecord::query()
            ->where('patient_user_id', $patient->id)
            ->where('vital_type', $type)
            ->latest('measured_at')
            ->first();

        return [
            'vital_type' => $type->value,
            'unit' => $latest?->unit,
            'range' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'group_by' => $groupBy,
            ],
            'points' => $points,
            'latest_record' => $latest,
            'flags_summary' => $records->countBy(fn (VitalRecord $record) => $record->flag->value)->all(),
        ];
    }

    public function latest(User $patient): Collection
    {
        return collect(VitalType::cases())
            ->map(fn (VitalType $type) => VitalRecord::query()
                ->where('patient_user_id', $patient->id)
                ->where('vital_type', $type)
                ->latest('measured_at')
                ->first())
            ->filter()
            ->values();
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

    private function groupKey(VitalRecord $record, string $groupBy): string
    {
        $date = $record->measured_at->toImmutable();

        return match ($groupBy) {
            'week' => $date->startOfWeek()->toDateString(),
            'month' => $date->startOfMonth()->toDateString(),
            default => $date->toDateString(),
        };
    }
}
