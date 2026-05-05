<?php

namespace App\Modules\Medications\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Medications\Application\Services\MedicationReminderService;
use App\Modules\Medications\Http\Requests\MedicationReminderRequest;
use App\Modules\Medications\Http\Resources\MedicationReminderResource;
use App\Modules\Medications\Infrastructure\Models\MedicationReminder;
use Illuminate\Http\Request;

class MedicationReminderController extends ApiController
{
    public function __construct(private readonly MedicationReminderService $reminders) {}

    public function index(Request $request)
    {
        $reminders = MedicationReminder::query()
            ->with('times')
            ->where('patient_user_id', $request->user()->id)
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('id')
            ->get();

        return $this->success(MedicationReminderResource::collection($reminders), 'Medication reminders.');
    }

    public function store(MedicationReminderRequest $request)
    {
        $reminder = $this->reminders->create($request->user(), $request->validated());

        return $this->success(new MedicationReminderResource($reminder), 'Medication reminder created.', 201);
    }

    public function show(MedicationReminder $reminder)
    {
        $this->authorize('view', $reminder);

        return $this->success(new MedicationReminderResource($reminder->load('times')), 'Medication reminder details.');
    }

    public function update(MedicationReminderRequest $request, MedicationReminder $reminder)
    {
        $this->authorize('update', $reminder);
        $reminder = $this->reminders->update($request->user(), $reminder, $request->validated());

        return $this->success(new MedicationReminderResource($reminder), 'Medication reminder updated.');
    }

    public function destroy(Request $request, MedicationReminder $reminder)
    {
        $this->authorize('delete', $reminder);
        $this->reminders->delete($request->user(), $reminder);

        return $this->success(null, 'Medication reminder deleted.');
    }

    public function pause(Request $request, MedicationReminder $reminder)
    {
        $this->authorize('update', $reminder);

        return $this->success(new MedicationReminderResource($this->reminders->pause($request->user(), $reminder)), 'Medication reminder paused.');
    }

    public function resume(Request $request, MedicationReminder $reminder)
    {
        $this->authorize('update', $reminder);

        return $this->success(new MedicationReminderResource($this->reminders->resume($request->user(), $reminder)), 'Medication reminder resumed.');
    }

    public function cancel(Request $request, MedicationReminder $reminder)
    {
        $this->authorize('update', $reminder);

        return $this->success(new MedicationReminderResource($this->reminders->cancel($request->user(), $reminder)), 'Medication reminder cancelled.');
    }
}
