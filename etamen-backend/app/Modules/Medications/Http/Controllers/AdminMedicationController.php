<?php

namespace App\Modules\Medications\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Medications\Http\Resources\MedicationLogResource;
use App\Modules\Medications\Http\Resources\MedicationNotificationQueueResource;
use App\Modules\Medications\Http\Resources\MedicationRefillEventResource;
use App\Modules\Medications\Http\Resources\MedicationReminderResource;
use App\Modules\Medications\Infrastructure\Models\MedicationLog;
use App\Modules\Medications\Infrastructure\Models\MedicationNotificationQueue;
use App\Modules\Medications\Infrastructure\Models\MedicationRefillEvent;
use App\Modules\Medications\Infrastructure\Models\MedicationReminder;
use Illuminate\Http\Request;

class AdminMedicationController extends ApiController
{
    public function reminders(Request $request)
    {
        $reminders = MedicationReminder::query()
            ->with('times')
            ->when($request->query('patient_user_id'), fn ($query, $id) => $query->where('patient_user_id', $id))
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('id')
            ->get();

        return $this->success(MedicationReminderResource::collection($reminders), 'Medication reminders.');
    }

    public function reminder(MedicationReminder $reminder)
    {
        return $this->success(new MedicationReminderResource($reminder->load('times')), 'Medication reminder details.');
    }

    public function logs(Request $request)
    {
        $logs = MedicationLog::query()
            ->when($request->query('patient_user_id'), fn ($query, $id) => $query->where('patient_user_id', $id))
            ->when($request->query('action'), fn ($query, $action) => $query->where('action', $action))
            ->orderByDesc('scheduled_for')
            ->get();

        return $this->success(MedicationLogResource::collection($logs), 'Medication logs.');
    }

    public function refillEvents(Request $request)
    {
        $events = MedicationRefillEvent::query()
            ->when($request->query('patient_user_id'), fn ($query, $id) => $query->where('patient_user_id', $id))
            ->orderByDesc('event_date')
            ->get();

        return $this->success(MedicationRefillEventResource::collection($events), 'Medication refill events.');
    }

    public function notificationQueue(Request $request)
    {
        $queue = MedicationNotificationQueue::query()
            ->when($request->query('patient_user_id'), fn ($query, $id) => $query->where('patient_user_id', $id))
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('scheduled_for')
            ->get();

        return $this->success(MedicationNotificationQueueResource::collection($queue), 'Medication notification queue.');
    }
}
