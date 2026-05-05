<?php

namespace App\Modules\Medications\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Medications\Application\Services\MedicationAdherenceService;
use App\Modules\Medications\Application\Services\MedicationScheduleService;
use App\Modules\Medications\Http\Resources\MedicationAdherenceResource;
use App\Modules\Medications\Infrastructure\Models\MedicationReminder;
use Illuminate\Http\Request;

class MedicationScheduleController extends ApiController
{
    public function __construct(
        private readonly MedicationScheduleService $schedule,
        private readonly MedicationAdherenceService $adherence,
    ) {}

    public function reminderSchedule(Request $request, MedicationReminder $reminder)
    {
        $this->authorize('view', $reminder);
        $occurrences = $this->schedule->occurrences($reminder, $request->query('from'), $request->query('to'))
            ->map(fn (array $item) => [
                ...$item,
                'scheduled_for' => $item['scheduled_for']->toISOString(),
            ])
            ->values();

        return $this->success($occurrences, 'Medication reminder schedule.');
    }

    public function today(Request $request)
    {
        $occurrences = $this->schedule->today($request->user())
            ->map(fn (array $item) => [
                ...$item,
                'scheduled_for' => $item['scheduled_for']->toISOString(),
            ]);

        return $this->success($occurrences, 'Today medications.');
    }

    public function upcoming(Request $request)
    {
        $days = (int) $request->query('days', 7);
        $occurrences = $this->schedule->upcoming($request->user(), $days)
            ->map(fn (array $item) => [
                ...$item,
                'scheduled_for' => $item['scheduled_for']->toISOString(),
            ]);

        return $this->success($occurrences, 'Upcoming medications.');
    }

    public function adherence(Request $request)
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        return $this->success(new MedicationAdherenceResource($this->adherence->summary($request->user(), $filters)), 'Medication adherence summary.');
    }
}
