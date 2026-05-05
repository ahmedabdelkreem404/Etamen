<?php

namespace App\Modules\Medications\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Medications\Application\Services\MedicationReminderTimeService;
use App\Modules\Medications\Http\Requests\MedicationReminderTimeRequest;
use App\Modules\Medications\Http\Resources\MedicationReminderTimeResource;
use App\Modules\Medications\Infrastructure\Models\MedicationReminder;
use App\Modules\Medications\Infrastructure\Models\MedicationReminderTime;
use Illuminate\Http\Request;

class MedicationReminderTimeController extends ApiController
{
    public function __construct(private readonly MedicationReminderTimeService $times) {}

    public function index(MedicationReminder $reminder)
    {
        $this->authorize('view', $reminder);

        return $this->success(MedicationReminderTimeResource::collection($reminder->times()->get()), 'Medication reminder times.');
    }

    public function store(MedicationReminderTimeRequest $request, MedicationReminder $reminder)
    {
        $this->authorize('update', $reminder);
        $time = $this->times->create($request->user(), $reminder, $request->validated());

        return $this->success(new MedicationReminderTimeResource($time), 'Medication reminder time created.', 201);
    }

    public function update(MedicationReminderTimeRequest $request, MedicationReminder $reminder, MedicationReminderTime $time)
    {
        abort_unless((int) $time->medication_reminder_id === (int) $reminder->id, 404);
        $this->authorize('update', $time);

        return $this->success(new MedicationReminderTimeResource($this->times->update($request->user(), $time, $request->validated())), 'Medication reminder time updated.');
    }

    public function destroy(Request $request, MedicationReminder $reminder, MedicationReminderTime $time)
    {
        abort_unless((int) $time->medication_reminder_id === (int) $reminder->id, 404);
        $this->authorize('delete', $time);

        return $this->success(new MedicationReminderTimeResource($this->times->deactivate($request->user(), $time)), 'Medication reminder time deactivated.');
    }
}
