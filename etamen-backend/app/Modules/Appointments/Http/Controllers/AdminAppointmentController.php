<?php

namespace App\Modules\Appointments\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Appointments\Application\Services\AdminAppointmentService;
use App\Modules\Appointments\Http\Requests\ForceCancelAppointmentRequest;
use App\Modules\Appointments\Http\Resources\AppointmentResource;
use App\Modules\Appointments\Http\Resources\AppointmentStatusHistoryResource;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use Illuminate\Http\Request;

class AdminAppointmentController extends ApiController
{
    public function __construct(private readonly AdminAppointmentService $adminAppointmentService) {}

    public function index(Request $request)
    {
        $appointments = Appointment::query()
            ->with(['slot', 'patient', 'doctorProfile.provider', 'provider', 'branch'])
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('provider_id'), fn ($query, $providerId) => $query->where('provider_id', $providerId))
            ->when($request->query('doctor_profile_id'), fn ($query, $doctorProfileId) => $query->where('doctor_profile_id', $doctorProfileId))
            ->when($request->query('patient_user_id'), fn ($query, $patientUserId) => $query->where('patient_user_id', $patientUserId))
            ->when($request->query('date_from'), fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($request->query('date_to'), fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->orderByDesc('created_at')
            ->limit($this->perPage($request, 50))
            ->get();

        return $this->success(AppointmentResource::collection($appointments), 'All appointments.');
    }

    public function show(Appointment $appointment)
    {
        return $this->success(
            new AppointmentResource($appointment->load(['slot', 'patient', 'doctorProfile.provider', 'provider', 'branch', 'statusHistories', 'review'])),
            'Appointment details.',
        );
    }

    public function statusHistory(Appointment $appointment)
    {
        return $this->success(
            AppointmentStatusHistoryResource::collection($appointment->statusHistories()->with('actor')->orderBy('id')->get()),
            'Appointment status history.',
        );
    }

    public function forceCancel(ForceCancelAppointmentRequest $request, Appointment $appointment)
    {
        $appointment = $this->adminAppointmentService->forceCancel(
            $request->user(),
            $appointment,
            $request->validated('reason'),
        );

        return $this->success(new AppointmentResource($appointment->load(['slot', 'statusHistories'])), 'Appointment force-cancelled.');
    }
}
