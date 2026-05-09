<?php

namespace App\Modules\Appointments\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Appointments\Application\Services\AppointmentReviewService;
use App\Modules\Appointments\Application\Services\BookAppointmentService;
use App\Modules\Appointments\Application\Services\PatientAppointmentService;
use App\Modules\Appointments\Http\Requests\BookAppointmentRequest;
use App\Modules\Appointments\Http\Requests\CancelAppointmentRequest;
use App\Modules\Appointments\Http\Requests\StoreAppointmentReviewRequest;
use App\Modules\Appointments\Http\Resources\AppointmentResource;
use App\Modules\Appointments\Http\Resources\AppointmentReviewResource;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use Illuminate\Http\Request;

class PatientAppointmentController extends ApiController
{
    public function __construct(
        private readonly BookAppointmentService $bookAppointmentService,
        private readonly PatientAppointmentService $patientAppointmentService,
        private readonly AppointmentReviewService $reviewService,
    ) {}

    public function store(BookAppointmentRequest $request)
    {
        $appointment = $this->bookAppointmentService->book($request->user(), $request->validated());

        return $this->success(new AppointmentResource($appointment), 'Appointment booked.', 201);
    }

    public function index(Request $request)
    {
        $appointments = Appointment::query()
            ->where('patient_user_id', $request->user()->id)
            ->with(['slot', 'doctorProfile.provider', 'provider', 'branch', 'hospital', 'hospitalDepartment', 'hospitalDoctorLink', 'review'])
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->limit($this->perPage($request))
            ->get();

        return $this->success(AppointmentResource::collection($appointments), 'Patient appointments.');
    }

    public function show(Request $request, Appointment $appointment)
    {
        $this->authorize('view', $appointment);

        return $this->success(
            new AppointmentResource($appointment->load(['slot', 'doctorProfile.provider', 'provider', 'branch', 'hospital', 'hospitalDepartment', 'hospitalDoctorLink', 'statusHistories', 'review'])),
            'Appointment details.',
        );
    }

    public function cancel(CancelAppointmentRequest $request, Appointment $appointment)
    {
        $this->authorize('cancel', $appointment);

        $appointment = $this->patientAppointmentService->cancel($request->user(), $appointment, $request->validated('reason'));

        return $this->success(new AppointmentResource($appointment->load(['slot', 'statusHistories'])), 'Appointment cancelled.');
    }

    public function review(StoreAppointmentReviewRequest $request, Appointment $appointment)
    {
        $this->authorize('review', $appointment);

        $review = $this->reviewService->create($request->user(), $appointment, $request->validated());

        return $this->success(new AppointmentReviewResource($review), 'Appointment review created.', 201);
    }
}
