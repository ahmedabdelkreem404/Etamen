<?php

namespace App\Modules\Appointments\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Appointments\Application\Services\DoctorAppointmentActionService;
use App\Modules\Appointments\Application\Services\DoctorBookingContextService;
use App\Modules\Appointments\Http\Requests\RejectAppointmentRequest;
use App\Modules\Appointments\Http\Resources\AppointmentResource;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use Illuminate\Http\Request;

class ProviderAppointmentController extends ApiController
{
    public function __construct(
        private readonly DoctorBookingContextService $contextService,
        private readonly DoctorAppointmentActionService $actionService,
    ) {}

    public function index(Request $request)
    {
        $doctor = $this->contextService->doctorForUser($request->user());

        $appointments = $doctor->appointments()
            ->with(['slot', 'patient', 'provider', 'branch', 'review'])
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->get();

        return $this->success(AppointmentResource::collection($appointments), 'Doctor appointments.');
    }

    public function show(Request $request, Appointment $appointment)
    {
        $this->authorize('doctorManage', $appointment);

        return $this->success(
            new AppointmentResource($appointment->load(['slot', 'patient', 'provider', 'branch', 'statusHistories', 'review'])),
            'Doctor appointment details.',
        );
    }

    public function accept(Request $request, Appointment $appointment)
    {
        $this->authorize('doctorManage', $appointment);

        $appointment = $this->actionService->accept($request->user(), $appointment);

        return $this->success(new AppointmentResource($appointment->load(['slot', 'statusHistories'])), 'Appointment accepted.');
    }

    public function reject(RejectAppointmentRequest $request, Appointment $appointment)
    {
        $this->authorize('doctorManage', $appointment);

        $appointment = $this->actionService->reject($request->user(), $appointment, $request->validated('reason'));

        return $this->success(new AppointmentResource($appointment->load(['slot', 'statusHistories'])), 'Appointment rejected.');
    }

    public function complete(Request $request, Appointment $appointment)
    {
        $this->authorize('doctorManage', $appointment);

        $appointment = $this->actionService->complete($request->user(), $appointment);

        return $this->success(new AppointmentResource($appointment->load(['slot', 'statusHistories'])), 'Appointment completed.');
    }

    public function noShow(Request $request, Appointment $appointment)
    {
        $this->authorize('doctorManage', $appointment);

        $appointment = $this->actionService->markNoShow($request->user(), $appointment, $request->input('reason'));

        return $this->success(new AppointmentResource($appointment->load(['slot', 'statusHistories'])), 'Appointment marked as no-show.');
    }
}
