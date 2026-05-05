<?php

namespace App\Modules\Medications\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Medications\Application\Services\MedicationLogService;
use App\Modules\Medications\Domain\Enums\MedicationLogAction;
use App\Modules\Medications\Http\Requests\MedicationLogRequest;
use App\Modules\Medications\Http\Requests\QuickMedicationLogRequest;
use App\Modules\Medications\Http\Resources\MedicationLogResource;
use App\Modules\Medications\Infrastructure\Models\MedicationLog;
use App\Modules\Medications\Infrastructure\Models\MedicationReminder;
use Illuminate\Http\Request;

class MedicationLogController extends ApiController
{
    public function __construct(private readonly MedicationLogService $logs) {}

    public function index(Request $request)
    {
        $logs = MedicationLog::query()
            ->where('patient_user_id', $request->user()->id)
            ->when($request->query('medication_reminder_id'), fn ($query, $id) => $query->where('medication_reminder_id', $id))
            ->when($request->query('from'), fn ($query, $date) => $query->whereDate('scheduled_for', '>=', $date))
            ->when($request->query('to'), fn ($query, $date) => $query->whereDate('scheduled_for', '<=', $date))
            ->orderByDesc('scheduled_for')
            ->get();

        return $this->success(MedicationLogResource::collection($logs), 'Medication logs.');
    }

    public function store(MedicationLogRequest $request, MedicationReminder $reminder)
    {
        $this->authorize('update', $reminder);
        $log = $this->logs->record($request->user(), $reminder, $request->validated());

        return $this->success(new MedicationLogResource($log), 'Medication log recorded.', 201);
    }

    public function update(MedicationLogRequest $request, MedicationLog $log)
    {
        $this->authorize('update', $log);
        $log = $this->logs->update($request->user(), $log, $request->validated());

        return $this->success(new MedicationLogResource($log), 'Medication log updated.');
    }

    public function taken(QuickMedicationLogRequest $request, MedicationReminder $reminder)
    {
        $this->authorize('update', $reminder);
        $log = $this->logs->record($request->user(), $reminder, $request->validated(), MedicationLogAction::Taken);

        return $this->success(new MedicationLogResource($log), 'Medication marked as taken.');
    }

    public function skipped(QuickMedicationLogRequest $request, MedicationReminder $reminder)
    {
        $this->authorize('update', $reminder);
        $log = $this->logs->record($request->user(), $reminder, $request->validated(), MedicationLogAction::Skipped);

        return $this->success(new MedicationLogResource($log), 'Medication marked as skipped.');
    }
}
