<?php

namespace App\Modules\Medications\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Medications\Application\Services\MedicationRefillService;
use App\Modules\Medications\Domain\Enums\MedicationRefillEventType;
use App\Modules\Medications\Http\Requests\MedicationRefillEventRequest;
use App\Modules\Medications\Http\Resources\MedicationRefillEventResource;
use App\Modules\Medications\Infrastructure\Models\MedicationRefillEvent;
use App\Modules\Medications\Infrastructure\Models\MedicationReminder;
use Illuminate\Http\Request;

class MedicationRefillController extends ApiController
{
    public function __construct(private readonly MedicationRefillService $refills) {}

    public function index(Request $request)
    {
        $events = MedicationRefillEvent::query()
            ->where('patient_user_id', $request->user()->id)
            ->when($request->query('from'), fn ($query, $date) => $query->whereDate('event_date', '>=', $date))
            ->when($request->query('to'), fn ($query, $date) => $query->whereDate('event_date', '<=', $date))
            ->orderByDesc('event_date')
            ->get();

        return $this->success(MedicationRefillEventResource::collection($events), 'Medication refill events.');
    }

    public function done(MedicationRefillEventRequest $request, MedicationReminder $reminder)
    {
        $this->authorize('update', $reminder);
        $event = $this->refills->record($request->user(), $reminder, MedicationRefillEventType::RefillDone, $request->validated());

        return $this->success(new MedicationRefillEventResource($event), 'Medication refill done recorded.', 201);
    }

    public function skipped(MedicationRefillEventRequest $request, MedicationReminder $reminder)
    {
        $this->authorize('update', $reminder);
        $event = $this->refills->record($request->user(), $reminder, MedicationRefillEventType::RefillSkipped, $request->validated());

        return $this->success(new MedicationRefillEventResource($event), 'Medication refill skipped recorded.', 201);
    }
}
