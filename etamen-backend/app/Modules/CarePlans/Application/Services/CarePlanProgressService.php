<?php

namespace App\Modules\CarePlans\Application\Services;

use App\Models\User;
use App\Modules\CarePlans\Domain\Enums\MealLogStatus;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanCheckin;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanMeal;
use App\Modules\CarePlans\Infrastructure\Models\MealLog;
use Carbon\CarbonImmutable;

class CarePlanProgressService
{
    public function progress(CarePlan $plan, array $filters = []): array
    {
        [$from, $to] = $this->range($plan, $filters['from'] ?? null, $filters['to'] ?? null);
        $checkins = CarePlanCheckin::query()
            ->where('care_plan_id', $plan->id)
            ->whereBetween('checkin_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('checkin_date')
            ->get();
        $mealLogs = MealLog::query()
            ->with(['meal', 'photo'])
            ->where('care_plan_id', $plan->id)
            ->whereBetween('logged_at', [$from, $to])
            ->orderByDesc('logged_at')
            ->get();
        $requiredMeals = $this->plannedRequiredMealsInRange($plan, $from, $to);

        $followed = $mealLogs->where('status', MealLogStatus::Followed)->count();
        $partial = $mealLogs->where('status', MealLogStatus::PartiallyFollowed)->count();
        $skipped = $mealLogs->where('status', MealLogStatus::Skipped)->count();
        $replaced = $mealLogs->where('status', MealLogStatus::Replaced)->count();
        $extra = $mealLogs->where('status', MealLogStatus::ExtraMeal)->count();
        $points = $followed + ($partial * 0.5);

        return [
            'plan_id' => $plan->id,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'days_count' => max(1, $from->diffInDays($to) + 1),
            'checkins_count' => $checkins->count(),
            'average_commitment_score' => $checkins->whereNotNull('commitment_score')->isNotEmpty()
                ? round((float) $checkins->avg('commitment_score'), 2)
                : null,
            'meal_logs_count' => $mealLogs->count(),
            'followed_count' => $followed,
            'partially_followed_count' => $partial,
            'skipped_count' => $skipped,
            'replaced_count' => $replaced,
            'extra_meal_count' => $extra,
            'planned_required_meals_count' => $requiredMeals,
            'adherence_percentage' => $requiredMeals > 0 ? round(($points / $requiredMeals) * 100, 2) : null,
            'latest_checkin' => $checkins->sortByDesc('checkin_date')->first(),
            'latest_meal_logs' => $mealLogs->take(5)->values(),
            'safe_disclaimer' => CarePlan::PROGRESS_DISCLAIMER,
        ];
    }

    public function summary(User $patient): array
    {
        $plans = CarePlan::query()
            ->where('patient_user_id', $patient->id)
            ->withCount(['checkins', 'mealLogs'])
            ->orderByDesc('id')
            ->get();

        return [
            'total_plans' => $plans->count(),
            'active_plans' => $plans->filter(fn (CarePlan $plan) => $plan->status->value === 'active')->count(),
            'plans' => $plans,
            'safe_disclaimer' => CarePlan::PROGRESS_DISCLAIMER,
        ];
    }

    private function range(CarePlan $plan, ?string $from, ?string $to): array
    {
        $end = $to ? CarbonImmutable::parse($to)->endOfDay() : now()->toImmutable()->endOfDay();
        $start = $from ? CarbonImmutable::parse($from)->startOfDay() : $end->subDays(30)->startOfDay();
        $planStart = CarbonImmutable::parse($plan->start_date)->startOfDay();
        $planEnd = $plan->end_date ? CarbonImmutable::parse($plan->end_date)->endOfDay() : null;

        if ($start->lt($planStart)) {
            $start = $planStart;
        }

        if ($planEnd && $end->gt($planEnd)) {
            $end = $planEnd;
        }

        if ($start->diffInDays($end) > 365) {
            $start = $end->subDays(365)->startOfDay();
        }

        return [$start, $end];
    }

    private function plannedRequiredMealsInRange(CarePlan $plan, CarbonImmutable $from, CarbonImmutable $to): int
    {
        return CarePlanMeal::query()
            ->where('is_required', true)
            ->whereHas('day', function ($query) use ($plan, $from, $to): void {
                $query->where('care_plan_id', $plan->id)
                    ->where(function ($inner) use ($from, $to): void {
                        $inner->whereNull('day_date')
                            ->orWhereBetween('day_date', [$from->toDateString(), $to->toDateString()]);
                    });
            })
            ->count();
    }
}
