<?php

namespace App\Modules\Appointments\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Appointments\Application\Services\AdminAppointmentService;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Http\Requests\ForceCancelAppointmentRequest;
use App\Modules\Appointments\Http\Resources\AppointmentResource;
use App\Modules\Appointments\Http\Resources\AppointmentStatusHistoryResource;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminAppointmentController extends ApiController
{
    public function __construct(private readonly AdminAppointmentService $adminAppointmentService) {}

    public function index(Request $request)
    {
        $appointments = Appointment::query()
            ->with(['slot', 'patient', 'doctorProfile.provider', 'provider', 'branch', 'hospital', 'hospitalDepartment', 'hospitalDoctorLink'])
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('provider_id'), fn ($query, $providerId) => $query->where('provider_id', $providerId))
            ->when($request->query('hospital_provider_id'), fn ($query, $hospitalProviderId) => $query->where('hospital_provider_id', $hospitalProviderId))
            ->when($request->query('hospital_department_id'), fn ($query, $hospitalDepartmentId) => $query->where('hospital_department_id', $hospitalDepartmentId))
            ->when($request->query('booked_through_hospital') !== null, fn ($query) => $request->boolean('booked_through_hospital') ? $query->whereNotNull('hospital_provider_id') : $query->whereNull('hospital_provider_id'))
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
            new AppointmentResource($appointment->load(['slot', 'patient', 'doctorProfile.provider', 'provider', 'branch', 'hospital', 'hospitalDepartment', 'hospitalDoctorLink', 'statusHistories', 'review'])),
            'Appointment details.',
        );
    }

    public function hospitalAppointments(Request $request, Provider $hospital)
    {
        $this->ensureHospitalProvider($hospital);

        $appointments = Appointment::query()
            ->with(['slot', 'patient', 'doctorProfile.provider', 'provider', 'branch', 'hospital', 'hospitalDepartment', 'hospitalDoctorLink'])
            ->where('hospital_provider_id', $hospital->id)
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('hospital_department_id'), fn ($query, $departmentId) => $query->where('hospital_department_id', $departmentId))
            ->orderByDesc('created_at')
            ->limit($this->perPage($request, 50))
            ->get();

        return $this->success(AppointmentResource::collection($appointments), 'Hospital appointments.');
    }

    public function hospitalSummary(Provider $hospital)
    {
        $this->ensureHospitalProvider($hospital);

        $base = Appointment::query()->where('hospital_provider_id', $hospital->id);
        $statusCount = fn (AppointmentStatus $status): int => (clone $base)
            ->where('status', $status->value)
            ->count();

        $grossAmount = (float) (clone $base)->sum('price');
        $verifiedPaidAmount = (float) (clone $base)
            ->whereHas('payment', fn ($query) => $query->where('status', PaymentStatus::Verified->value))
            ->sum('price');
        $pendingAmount = (float) (clone $base)
            ->whereIn('status', [
                AppointmentStatus::PendingPayment->value,
                AppointmentStatus::PendingPaymentReview->value,
            ])
            ->sum('price');

        return $this->success([
            'hospital' => [
                'id' => $hospital->id,
                'name_ar' => $hospital->name_ar,
                'name_en' => $hospital->name_en,
            ],
            'total_appointments' => (clone $base)->count(),
            'pending_payment' => $statusCount(AppointmentStatus::PendingPayment),
            'pending_payment_review' => $statusCount(AppointmentStatus::PendingPaymentReview),
            'confirmed' => $statusCount(AppointmentStatus::Confirmed),
            'completed' => $statusCount(AppointmentStatus::Completed),
            'cancelled' => (clone $base)
                ->whereIn('status', [
                    AppointmentStatus::CancelledByPatient->value,
                    AppointmentStatus::CancelledByDoctor->value,
                ])
                ->count(),
            'gross_amount' => number_format($grossAmount, 2, '.', ''),
            'verified_paid_amount' => number_format($verifiedPaidAmount, 2, '.', ''),
            'pending_amount' => number_format($pendingAmount, 2, '.', ''),
        ], 'Hospital appointment summary.');
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

    private function ensureHospitalProvider(Provider $hospital): void
    {
        if ($hospital->type !== ProviderType::Hospital) {
            throw ValidationException::withMessages([
                'hospital' => ['The selected provider is not a hospital.'],
            ]);
        }
    }
}
