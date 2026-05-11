<?php

namespace App\Modules\Providers\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Appointments\Application\Services\DoctorAppointmentActionService;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\Fitness\Application\Services\CoachBookingStatusService;
use App\Modules\Fitness\Application\Services\GymBookingStatusService;
use App\Modules\Fitness\Domain\Enums\CoachBookingStatus;
use App\Modules\Fitness\Domain\Enums\GymBookingStatus;
use App\Modules\Fitness\Infrastructure\Models\CoachAvailabilitySlot;
use App\Modules\Fitness\Infrastructure\Models\CoachBooking;
use App\Modules\Fitness\Infrastructure\Models\CoachPackage;
use App\Modules\Fitness\Infrastructure\Models\CoachSessionType;
use App\Modules\Fitness\Infrastructure\Models\GymBooking;
use App\Modules\Fitness\Infrastructure\Models\GymClassModel;
use App\Modules\Fitness\Infrastructure\Models\GymMembershipPlan;
use App\Modules\Labs\Application\Services\LabOrderService;
use App\Modules\Labs\Domain\Enums\LabOrderStatus;
use App\Modules\Labs\Infrastructure\Models\LabOrder;
use App\Modules\Labs\Infrastructure\Models\LabPackage;
use App\Modules\Labs\Infrastructure\Models\LabTest;
use App\Modules\Pharmacies\Application\Services\PharmacyOrderService;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderStatus;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyOrder;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyProduct;
use App\Modules\Providers\Application\Services\ProviderWorkspaceService;
use App\Modules\Providers\Domain\Enums\ProviderPermission;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\HospitalDepartment;
use App\Modules\Providers\Infrastructure\Models\HospitalDoctor;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderStaff;
use App\Modules\Radiology\Application\Services\RadiologyOrderService;
use App\Modules\Radiology\Infrastructure\Models\RadiologyOrder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use UnitEnum;

class ProviderWorkspaceOperationsController extends ApiController
{
    public function __construct(
        private readonly ProviderWorkspaceService $workspaceService,
        private readonly DoctorAppointmentActionService $doctorAppointmentActions,
        private readonly RadiologyOrderService $radiologyOrders,
        private readonly GymBookingStatusService $gymBookingStatus,
        private readonly CoachBookingStatusService $coachBookingStatus,
        private readonly PharmacyOrderService $pharmacyOrdersService,
        private readonly LabOrderService $labOrdersService,
    ) {}

    public function doctorAppointments(Request $request, Provider $provider): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ViewAppointments, ProviderType::Doctor);

        $items = Appointment::query()
            ->with(['patient', 'slot', 'branch', 'payment.paymentMethod', 'hospital', 'hospitalDepartment'])
            ->where('provider_id', $provider->id)
            ->latest()
            ->limit($this->perPage($request))
            ->get()
            ->map(fn (Appointment $appointment): array => $this->appointmentPayload($appointment))
            ->values()
            ->all();

        return $this->success($this->listPayload($items));
    }

    public function doctorAppointment(Request $request, Provider $provider, Appointment $appointment): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ViewAppointments, ProviderType::Doctor);
        $appointment = $this->doctorAppointmentForProvider($provider, $appointment);

        return $this->success($this->appointmentPayload($appointment));
    }

    public function confirmDoctorAppointment(Request $request, Provider $provider, Appointment $appointment): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ManageAppointments, ProviderType::Doctor);
        $appointment = $this->doctorAppointmentForProvider($provider, $appointment);

        return $this->success($this->appointmentPayload(
            $this->doctorAppointmentActions->accept($request->user(), $appointment)->load(['patient', 'slot', 'branch', 'payment.paymentMethod', 'hospital', 'hospitalDepartment'])
        ));
    }

    public function completeDoctorAppointment(Request $request, Provider $provider, Appointment $appointment): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ManageAppointments, ProviderType::Doctor);
        $appointment = $this->doctorAppointmentForProvider($provider, $appointment);

        return $this->success($this->appointmentPayload(
            $this->doctorAppointmentActions->complete($request->user(), $appointment)->load(['patient', 'slot', 'branch', 'payment.paymentMethod', 'hospital', 'hospitalDepartment'])
        ));
    }

    public function cancelDoctorAppointment(Request $request, Provider $provider, Appointment $appointment): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ManageAppointments, ProviderType::Doctor);
        $appointment = $this->doctorAppointmentForProvider($provider, $appointment);

        return $this->success($this->appointmentPayload(
            $this->doctorAppointmentActions->reject(
                $request->user(),
                $appointment,
                $request->string('reason')->toString() ?: 'Cancelled from provider workspace.'
            )->load(['patient', 'slot', 'branch', 'payment.paymentMethod', 'hospital', 'hospitalDepartment'])
        ));
    }

    public function hospitalAppointments(Request $request, Provider $provider): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ViewBookings, ProviderType::Hospital);

        $items = Appointment::query()
            ->with(['patient', 'provider', 'hospitalDepartment', 'hospitalDoctorLink.doctorProvider', 'payment.paymentMethod'])
            ->where('hospital_provider_id', $provider->id)
            ->latest()
            ->limit($this->perPage($request))
            ->get()
            ->map(fn (Appointment $appointment): array => $this->appointmentPayload($appointment))
            ->values()
            ->all();

        return $this->success($this->listPayload($items));
    }

    public function hospitalDepartments(Request $request, Provider $provider): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ManageDepartments, ProviderType::Hospital);

        $items = HospitalDepartment::query()
            ->where('hospital_provider_id', $provider->id)
            ->withCount(['doctors as active_doctors_count' => fn ($query) => $query->where('is_active', true)])
            ->orderByDesc('is_active')
            ->orderBy('id')
            ->limit($this->perPage($request, 50))
            ->get()
            ->map(fn (HospitalDepartment $department): array => $this->hospitalDepartmentPayload($department))
            ->values()
            ->all();

        return $this->success($this->listPayload($items));
    }

    public function hospitalDoctors(Request $request, Provider $provider): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ManageHospitalDoctors, ProviderType::Hospital);

        $items = HospitalDoctor::query()
            ->with(['doctorProvider.doctorProfile', 'department'])
            ->where('hospital_provider_id', $provider->id)
            ->orderByDesc('is_active')
            ->orderBy('id')
            ->limit($this->perPage($request, 50))
            ->get()
            ->map(fn (HospitalDoctor $link): array => $this->hospitalDoctorPayload($link))
            ->values()
            ->all();

        return $this->success($this->listPayload($items));
    }

    public function radiologyOrders(Request $request, Provider $provider): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ViewRadiologyOrders, ProviderType::Radiology);

        $items = RadiologyOrder::query()
            ->with(['patient', 'branch', 'payment.paymentMethod', 'items', 'results'])
            ->where('provider_id', $provider->id)
            ->latest()
            ->limit($this->perPage($request))
            ->get()
            ->map(fn (RadiologyOrder $order): array => $this->radiologyOrderPayload($order))
            ->values()
            ->all();

        return $this->success($this->listPayload($items));
    }

    public function radiologyOrder(Request $request, Provider $provider, RadiologyOrder $order): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ViewRadiologyOrders, ProviderType::Radiology);
        $order = $this->radiologyOrderForProvider($provider, $order);

        return $this->success($this->radiologyOrderPayload($order));
    }

    public function acceptRadiologyOrder(Request $request, Provider $provider, RadiologyOrder $order): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ManageRadiologyOrders, ProviderType::Radiology);
        $order = $this->radiologyOrderForProvider($provider, $order);

        return $this->success($this->radiologyOrderPayload($this->radiologyOrders->accept($request->user(), $order)));
    }

    public function rejectRadiologyOrder(Request $request, Provider $provider, RadiologyOrder $order): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ManageRadiologyOrders, ProviderType::Radiology);
        $order = $this->radiologyOrderForProvider($provider, $order);

        return $this->success($this->radiologyOrderPayload($this->radiologyOrders->reject(
            $request->user(),
            $order,
            $request->string('reason')->toString() ?: null
        )));
    }

    public function startRadiologyOrder(Request $request, Provider $provider, RadiologyOrder $order): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ManageRadiologyOrders, ProviderType::Radiology);
        $order = $this->radiologyOrderForProvider($provider, $order);

        return $this->success($this->radiologyOrderPayload($this->radiologyOrders->start($request->user(), $order)));
    }

    public function markRadiologyResultReady(Request $request, Provider $provider, RadiologyOrder $order): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ManageRadiologyOrders, ProviderType::Radiology);
        $order = $this->radiologyOrderForProvider($provider, $order);

        return $this->success($this->radiologyOrderPayload($this->radiologyOrders->markResultReady($request->user(), $order)));
    }

    public function completeRadiologyOrder(Request $request, Provider $provider, RadiologyOrder $order): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ManageRadiologyOrders, ProviderType::Radiology);
        $order = $this->radiologyOrderForProvider($provider, $order);

        return $this->success($this->radiologyOrderPayload($this->radiologyOrders->complete($request->user(), $order)));
    }

    public function pharmacyOrders(Request $request, Provider $provider): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ViewPharmacyOrders, ProviderType::Pharmacy);

        $items = PharmacyOrder::query()
            ->with(['patient', 'payment.paymentMethod', 'items'])
            ->where('pharmacy_provider_id', $provider->id)
            ->latest()
            ->limit($this->perPage($request))
            ->get()
            ->map(fn (PharmacyOrder $order): array => $this->pharmacyOrderPayload($order))
            ->values()
            ->all();

        return $this->success($this->listPayload($items));
    }

    public function pharmacyOrder(Request $request, Provider $provider, PharmacyOrder $order): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ViewPharmacyOrders, ProviderType::Pharmacy);
        $order = $this->pharmacyOrderForProvider($provider, $order);

        return $this->success($this->pharmacyOrderPayload($order));
    }

    public function acceptPharmacyOrder(Request $request, Provider $provider, PharmacyOrder $order): JsonResponse
    {
        return $this->transitionPharmacyOrder($request, $provider, $order, PharmacyOrderStatus::Accepted);
    }

    public function rejectPharmacyOrder(Request $request, Provider $provider, PharmacyOrder $order): JsonResponse
    {
        return $this->transitionPharmacyOrder($request, $provider, $order, PharmacyOrderStatus::Rejected, true);
    }

    public function markPharmacyPreparing(Request $request, Provider $provider, PharmacyOrder $order): JsonResponse
    {
        return $this->transitionPharmacyOrder($request, $provider, $order, PharmacyOrderStatus::Preparing);
    }

    public function markPharmacyReady(Request $request, Provider $provider, PharmacyOrder $order): JsonResponse
    {
        return $this->transitionPharmacyOrder($request, $provider, $order, PharmacyOrderStatus::ReadyForPickup);
    }

    public function markPharmacyOutForDelivery(Request $request, Provider $provider, PharmacyOrder $order): JsonResponse
    {
        return $this->transitionPharmacyOrder($request, $provider, $order, PharmacyOrderStatus::OutForDelivery);
    }

    public function completePharmacyOrder(Request $request, Provider $provider, PharmacyOrder $order): JsonResponse
    {
        return $this->transitionPharmacyOrder($request, $provider, $order, PharmacyOrderStatus::Delivered);
    }

    public function pharmacyProducts(Request $request, Provider $provider): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ManagePharmacyProducts, ProviderType::Pharmacy);

        $items = PharmacyProduct::query()
            ->where('provider_id', $provider->id)
            ->orderByDesc('is_active')
            ->orderBy('name_ar')
            ->limit($this->perPage($request, 50))
            ->get()
            ->map(fn (PharmacyProduct $product): array => $this->pharmacyProductPayload($product))
            ->values()
            ->all();

        return $this->success($this->listPayload($items));
    }

    public function labOrders(Request $request, Provider $provider): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ViewLabOrders, ProviderType::Lab);

        $items = LabOrder::query()
            ->with(['patient', 'payment.paymentMethod', 'items', 'results'])
            ->where('lab_provider_id', $provider->id)
            ->latest()
            ->limit($this->perPage($request))
            ->get()
            ->map(fn (LabOrder $order): array => $this->labOrderPayload($order))
            ->values()
            ->all();

        return $this->success($this->listPayload($items));
    }

    public function labOrder(Request $request, Provider $provider, LabOrder $order): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ViewLabOrders, ProviderType::Lab);
        $order = $this->labOrderForProvider($provider, $order);

        return $this->success($this->labOrderPayload($order));
    }

    public function acceptLabOrder(Request $request, Provider $provider, LabOrder $order): JsonResponse
    {
        return $this->transitionLabOrder($request, $provider, $order, LabOrderStatus::Accepted);
    }

    public function rejectLabOrder(Request $request, Provider $provider, LabOrder $order): JsonResponse
    {
        return $this->transitionLabOrder($request, $provider, $order, LabOrderStatus::Rejected, true);
    }

    public function scheduleLabSample(Request $request, Provider $provider, LabOrder $order): JsonResponse
    {
        return $this->transitionLabOrder($request, $provider, $order, LabOrderStatus::SampleScheduled);
    }

    public function markLabSampleCollected(Request $request, Provider $provider, LabOrder $order): JsonResponse
    {
        return $this->transitionLabOrder($request, $provider, $order, LabOrderStatus::SampleCollected);
    }

    public function markLabProcessing(Request $request, Provider $provider, LabOrder $order): JsonResponse
    {
        return $this->transitionLabOrder($request, $provider, $order, LabOrderStatus::Processing);
    }

    public function markLabResultReady(Request $request, Provider $provider, LabOrder $order): JsonResponse
    {
        return $this->transitionLabOrder($request, $provider, $order, LabOrderStatus::ResultReady);
    }

    public function completeLabOrder(Request $request, Provider $provider, LabOrder $order): JsonResponse
    {
        return $this->transitionLabOrder($request, $provider, $order, LabOrderStatus::Completed);
    }

    public function labCatalog(Request $request, Provider $provider): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ManageLabCatalog, ProviderType::Lab);

        $tests = LabTest::query()
            ->where('provider_id', $provider->id)
            ->orderByDesc('is_active')
            ->orderBy('name_ar')
            ->limit($this->perPage($request, 50))
            ->get()
            ->map(fn (LabTest $test): array => $this->labTestPayload($test))
            ->values()
            ->all();

        $packages = LabPackage::query()
            ->where('provider_id', $provider->id)
            ->orderByDesc('is_active')
            ->orderBy('name_ar')
            ->limit($this->perPage($request, 50))
            ->get()
            ->map(fn (LabPackage $package): array => $this->labPackagePayload($package))
            ->values()
            ->all();

        return $this->success([
            'tests' => $tests,
            'packages' => $packages,
            'meta' => [
                'tests_count' => count($tests),
                'packages_count' => count($packages),
            ],
        ]);
    }

    public function gymBookings(Request $request, Provider $provider): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ViewGymBookings, ProviderType::Gym);

        $items = GymBooking::query()
            ->with(['patient', 'membershipPlan', 'gymClass', 'payment.paymentMethod'])
            ->where('provider_id', $provider->id)
            ->latest()
            ->limit($this->perPage($request))
            ->get()
            ->map(fn (GymBooking $booking): array => $this->gymBookingPayload($booking))
            ->values()
            ->all();

        return $this->success($this->listPayload($items));
    }

    public function gymBooking(Request $request, Provider $provider, GymBooking $booking): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ViewGymBookings, ProviderType::Gym);
        $booking = $this->gymBookingForProvider($provider, $booking);

        return $this->success($this->gymBookingPayload($booking));
    }

    public function confirmGymBooking(Request $request, Provider $provider, GymBooking $booking): JsonResponse
    {
        return $this->transitionGymBooking($request, $provider, $booking, GymBookingStatus::Confirmed, [GymBookingStatus::Paid]);
    }

    public function activateGymBooking(Request $request, Provider $provider, GymBooking $booking): JsonResponse
    {
        return $this->transitionGymBooking($request, $provider, $booking, GymBookingStatus::Active, [GymBookingStatus::Paid, GymBookingStatus::Confirmed]);
    }

    public function completeGymBooking(Request $request, Provider $provider, GymBooking $booking): JsonResponse
    {
        return $this->transitionGymBooking($request, $provider, $booking, GymBookingStatus::Completed, [GymBookingStatus::Paid, GymBookingStatus::Confirmed, GymBookingStatus::Active], ['completed_at' => now()]);
    }

    public function cancelGymBooking(Request $request, Provider $provider, GymBooking $booking): JsonResponse
    {
        return $this->transitionGymBooking($request, $provider, $booking, GymBookingStatus::CancelledByProvider, [GymBookingStatus::PendingPayment, GymBookingStatus::PendingPaymentReview, GymBookingStatus::Paid, GymBookingStatus::Confirmed, GymBookingStatus::Active], ['cancelled_at' => now()]);
    }

    public function gymPlans(Request $request, Provider $provider): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ManageGymPlans, ProviderType::Gym);

        $items = GymMembershipPlan::query()
            ->where('provider_id', $provider->id)
            ->orderByDesc('is_active')
            ->orderBy('sort_order')
            ->limit($this->perPage($request, 50))
            ->get()
            ->map(fn (GymMembershipPlan $plan): array => $this->gymPlanPayload($plan))
            ->values()
            ->all();

        return $this->success($this->listPayload($items));
    }

    public function gymClasses(Request $request, Provider $provider): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ManageGymClasses, ProviderType::Gym);

        $items = GymClassModel::query()
            ->where('provider_id', $provider->id)
            ->orderByDesc('is_active')
            ->orderBy('starts_at')
            ->limit($this->perPage($request, 50))
            ->get()
            ->map(fn (GymClassModel $class): array => $this->gymClassPayload($class))
            ->values()
            ->all();

        return $this->success($this->listPayload($items));
    }

    public function coachBookings(Request $request, Provider $provider): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ViewCoachBookings, [ProviderType::FitnessCoach, ProviderType::NutritionCoach]);

        $items = CoachBooking::query()
            ->with(['patient', 'sessionType', 'availabilitySlot', 'payment.paymentMethod'])
            ->where('coach_provider_id', $provider->id)
            ->latest()
            ->limit($this->perPage($request))
            ->get()
            ->map(fn (CoachBooking $booking): array => $this->coachBookingPayload($booking))
            ->values()
            ->all();

        return $this->success($this->listPayload($items));
    }

    public function coachBooking(Request $request, Provider $provider, CoachBooking $booking): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ViewCoachBookings, [ProviderType::FitnessCoach, ProviderType::NutritionCoach]);
        $booking = $this->coachBookingForProvider($provider, $booking);

        return $this->success($this->coachBookingPayload($booking));
    }

    public function confirmCoachBooking(Request $request, Provider $provider, CoachBooking $booking): JsonResponse
    {
        return $this->transitionCoachBooking($request, $provider, $booking, CoachBookingStatus::Confirmed, [CoachBookingStatus::Paid]);
    }

    public function startCoachBooking(Request $request, Provider $provider, CoachBooking $booking): JsonResponse
    {
        return $this->transitionCoachBooking($request, $provider, $booking, CoachBookingStatus::InProgress, [CoachBookingStatus::Paid, CoachBookingStatus::Confirmed]);
    }

    public function completeCoachBooking(Request $request, Provider $provider, CoachBooking $booking): JsonResponse
    {
        return $this->transitionCoachBooking($request, $provider, $booking, CoachBookingStatus::Completed, [CoachBookingStatus::Paid, CoachBookingStatus::Confirmed, CoachBookingStatus::InProgress], ['completed_at' => now()]);
    }

    public function cancelCoachBooking(Request $request, Provider $provider, CoachBooking $booking): JsonResponse
    {
        return $this->transitionCoachBooking($request, $provider, $booking, CoachBookingStatus::CancelledByCoach, [CoachBookingStatus::PendingPayment, CoachBookingStatus::PendingPaymentReview, CoachBookingStatus::Paid, CoachBookingStatus::Confirmed, CoachBookingStatus::InProgress], ['cancelled_at' => now()]);
    }

    public function coachAvailability(Request $request, Provider $provider): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ManageCoachAvailability, [ProviderType::FitnessCoach, ProviderType::NutritionCoach]);

        $items = CoachAvailabilitySlot::query()
            ->where('provider_id', $provider->id)
            ->orderBy('starts_at')
            ->limit($this->perPage($request, 50))
            ->get()
            ->map(fn (CoachAvailabilitySlot $slot): array => $this->coachAvailabilityPayload($slot))
            ->values()
            ->all();

        return $this->success($this->listPayload($items));
    }

    public function coachSessionTypes(Request $request, Provider $provider): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ManageCoachSessions, [ProviderType::FitnessCoach, ProviderType::NutritionCoach]);

        $items = CoachSessionType::query()
            ->where('provider_id', $provider->id)
            ->orderByDesc('is_active')
            ->orderBy('sort_order')
            ->limit($this->perPage($request, 50))
            ->get()
            ->map(fn (CoachSessionType $type): array => $this->coachSessionTypePayload($type))
            ->values()
            ->all();

        return $this->success($this->listPayload($items));
    }

    public function coachPackages(Request $request, Provider $provider): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ManageCoachSessions, [ProviderType::FitnessCoach, ProviderType::NutritionCoach]);

        $items = CoachPackage::query()
            ->where('provider_id', $provider->id)
            ->orderByDesc('is_active')
            ->orderBy('name_ar')
            ->limit($this->perPage($request, 50))
            ->get()
            ->map(fn (CoachPackage $package): array => $this->coachPackagePayload($package))
            ->values()
            ->all();

        return $this->success($this->listPayload($items));
    }

    private function authorizeWorkspace(Request $request, Provider $provider, ProviderPermission $permission, ProviderType|array $types): ProviderStaff
    {
        $expectedTypes = is_array($types) ? $types : [$types];

        if (! in_array($provider->type, $expectedTypes, true)) {
            throw new AuthorizationException('This provider workspace does not support the requested operation.');
        }

        $staff = $this->workspaceService->activeStaffFor($request->user(), $provider);

        if (! $staff->hasPermission($permission)) {
            throw new AuthorizationException($permission->value.' permission is required.');
        }

        return $staff;
    }

    private function doctorAppointmentForProvider(Provider $provider, Appointment $appointment): Appointment
    {
        return Appointment::query()
            ->with(['patient', 'slot', 'branch', 'payment.paymentMethod', 'hospital', 'hospitalDepartment'])
            ->whereKey($appointment->id)
            ->where('provider_id', $provider->id)
            ->firstOrFail();
    }

    private function radiologyOrderForProvider(Provider $provider, RadiologyOrder $order): RadiologyOrder
    {
        return RadiologyOrder::query()
            ->with(['patient', 'branch', 'payment.paymentMethod', 'items', 'results'])
            ->whereKey($order->id)
            ->where('provider_id', $provider->id)
            ->firstOrFail();
    }

    private function pharmacyOrderForProvider(Provider $provider, PharmacyOrder $order): PharmacyOrder
    {
        return PharmacyOrder::query()
            ->with(['patient', 'payment.paymentMethod', 'items'])
            ->whereKey($order->id)
            ->where('pharmacy_provider_id', $provider->id)
            ->firstOrFail();
    }

    private function labOrderForProvider(Provider $provider, LabOrder $order): LabOrder
    {
        return LabOrder::query()
            ->with(['patient', 'payment.paymentMethod', 'items', 'results'])
            ->whereKey($order->id)
            ->where('lab_provider_id', $provider->id)
            ->firstOrFail();
    }

    private function gymBookingForProvider(Provider $provider, GymBooking $booking): GymBooking
    {
        return GymBooking::query()
            ->with(['patient', 'membershipPlan', 'gymClass', 'payment.paymentMethod'])
            ->whereKey($booking->id)
            ->where('provider_id', $provider->id)
            ->firstOrFail();
    }

    private function coachBookingForProvider(Provider $provider, CoachBooking $booking): CoachBooking
    {
        return CoachBooking::query()
            ->with(['patient', 'sessionType', 'availabilitySlot', 'payment.paymentMethod'])
            ->whereKey($booking->id)
            ->where('coach_provider_id', $provider->id)
            ->firstOrFail();
    }

    private function transitionGymBooking(Request $request, Provider $provider, GymBooking $booking, GymBookingStatus $to, array $allowedFrom, array $extra = []): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ManageGymBookings, ProviderType::Gym);
        $booking = $this->gymBookingForProvider($provider, $booking);
        $this->assertCurrentStatus($booking->status, $allowedFrom, 'This gym booking cannot move to the requested status.');

        $updated = DB::transaction(fn (): GymBooking => $this->gymBookingStatus->transition(
            $booking,
            $to,
            $request->user(),
            'provider_workspace.gym_booking.status_changed',
            $request->string('reason')->toString() ?: null,
            ['source' => 'provider_workspace'],
            $extra,
        )->load(['patient', 'membershipPlan', 'gymClass', 'payment.paymentMethod']));

        return $this->success($this->gymBookingPayload($updated));
    }

    private function transitionCoachBooking(Request $request, Provider $provider, CoachBooking $booking, CoachBookingStatus $to, array $allowedFrom, array $extra = []): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ManageCoachBookings, [ProviderType::FitnessCoach, ProviderType::NutritionCoach]);
        $booking = $this->coachBookingForProvider($provider, $booking);
        $this->assertCurrentStatus($booking->status, $allowedFrom, 'This coach booking cannot move to the requested status.');

        $updated = DB::transaction(fn (): CoachBooking => $this->coachBookingStatus->transition(
            $booking,
            $to,
            $request->user(),
            'provider_workspace.coach_booking.status_changed',
            $request->string('reason')->toString() ?: null,
            ['source' => 'provider_workspace'],
            $extra,
        )->load(['patient', 'sessionType', 'availabilitySlot', 'payment.paymentMethod']));

        return $this->success($this->coachBookingPayload($updated));
    }

    private function transitionPharmacyOrder(Request $request, Provider $provider, PharmacyOrder $order, PharmacyOrderStatus $to, bool $requiresReason = false): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ManagePharmacyOrders, ProviderType::Pharmacy);
        $order = $this->pharmacyOrderForProvider($provider, $order);
        $reason = $request->string('reason')->trim()->toString() ?: null;

        if ($requiresReason && ! $reason) {
            throw ValidationException::withMessages(['reason' => ['A reason is required for this pharmacy action.']]);
        }

        $updated = $this->pharmacyOrdersService
            ->providerWorkspaceUpdateStatus($request->user(), $provider, $order, $to, $reason)
            ->load(['patient', 'payment.paymentMethod', 'items']);

        return $this->success($this->pharmacyOrderPayload($updated));
    }

    private function transitionLabOrder(Request $request, Provider $provider, LabOrder $order, LabOrderStatus $to, bool $requiresReason = false): JsonResponse
    {
        $this->authorizeWorkspace($request, $provider, ProviderPermission::ManageLabOrders, ProviderType::Lab);
        $order = $this->labOrderForProvider($provider, $order);
        $reason = $request->string('reason')->trim()->toString() ?: null;

        if ($requiresReason && ! $reason) {
            throw ValidationException::withMessages(['reason' => ['A reason is required for this lab action.']]);
        }

        $updated = $this->labOrdersService
            ->providerWorkspaceUpdateStatus($request->user(), $provider, $order, $to, $reason)
            ->load(['patient', 'payment.paymentMethod', 'items', 'results']);

        return $this->success($this->labOrderPayload($updated));
    }

    private function assertCurrentStatus(UnitEnum|string|null $current, array $allowed, string $message): void
    {
        if (! in_array($current, $allowed, true)) {
            throw ValidationException::withMessages(['status' => [$message]]);
        }
    }

    private function appointmentPayload(Appointment $appointment): array
    {
        return [
            'id' => $appointment->id,
            'number' => $appointment->appointment_number,
            'status' => $this->wire($appointment->status),
            'booked_at' => $appointment->booked_at?->toISOString(),
            'price' => $this->decimal($appointment->price),
            'currency' => $appointment->currency,
            'patient' => $this->patientSummary($appointment->patient),
            'payment' => $this->paymentSummary($appointment->payment),
            'branch' => $appointment->branch ? [
                'id' => $appointment->branch->id,
                'name_ar' => $appointment->branch->name_ar,
                'name_en' => $appointment->branch->name_en,
            ] : null,
            'hospital_context' => $appointment->hospital_provider_id ? [
                'hospital_id' => $appointment->hospital_provider_id,
                'hospital_name_ar' => $appointment->hospital?->name_ar,
                'hospital_name_en' => $appointment->hospital?->name_en,
                'department_id' => $appointment->hospital_department_id,
                'department_name_ar' => $appointment->hospitalDepartment?->name_ar,
                'department_name_en' => $appointment->hospitalDepartment?->name_en,
            ] : null,
        ];
    }

    private function hospitalDepartmentPayload(HospitalDepartment $department): array
    {
        return [
            'id' => $department->id,
            'name_ar' => $department->name_ar,
            'name_en' => $department->name_en,
            'is_active' => (bool) $department->is_active,
            'active_doctors_count' => (int) ($department->active_doctors_count ?? 0),
        ];
    }

    private function hospitalDoctorPayload(HospitalDoctor $link): array
    {
        return [
            'id' => $link->id,
            'doctor_provider_id' => $link->doctor_provider_id,
            'doctor_name_ar' => $link->doctorProvider?->name_ar,
            'doctor_name_en' => $link->doctorProvider?->name_en,
            'department_id' => $link->hospital_department_id,
            'department_name_ar' => $link->department?->name_ar,
            'department_name_en' => $link->department?->name_en,
            'consultation_fee' => $this->decimal($link->consultation_fee),
            'is_active' => (bool) $link->is_active,
        ];
    }

    private function radiologyOrderPayload(RadiologyOrder $order): array
    {
        return [
            'id' => $order->id,
            'number' => $order->order_number,
            'status' => $this->wire($order->status),
            'scheduled_at' => $order->scheduled_at?->toISOString(),
            'total_amount' => $this->decimal($order->total_amount),
            'patient' => $this->patientSummary($order->patient),
            'payment' => $this->paymentSummary($order->payment),
            'items' => $order->items->map(fn ($item): array => [
                'id' => $item->id,
                'name_ar' => $item->scan_name_ar,
                'name_en' => $item->scan_name_en,
                'quantity' => (int) $item->quantity,
                'total_price' => $this->decimal($item->total_price),
            ])->values()->all(),
            'results' => $order->results->map(fn ($result): array => [
                'id' => $result->id,
                'title_ar' => $result->title_ar,
                'title_en' => $result->title_en,
                'result_type' => $this->wire($result->result_type),
                'is_visible_to_patient' => (bool) $result->is_visible_to_patient,
                'uploaded_at' => $result->uploaded_at?->toISOString(),
            ])->values()->all(),
        ];
    }

    private function pharmacyOrderPayload(PharmacyOrder $order): array
    {
        return [
            'id' => $order->id,
            'number' => $order->order_number,
            'status' => $this->wire($order->order_status),
            'payment_status' => $this->wire($order->payment_status),
            'grand_total' => $this->decimal($order->grand_total),
            'patient' => $this->patientSummary($order->patient),
            'payment' => $this->paymentSummary($order->payment),
            'requires_prescription' => (bool) $order->prescription_id,
            'items' => $order->items->map(fn ($item): array => [
                'id' => $item->id,
                'product_name' => $item->product_name,
                'quantity' => (int) $item->quantity,
                'line_total' => $this->decimal($item->line_total),
            ])->values()->all(),
        ];
    }

    private function pharmacyProductPayload(PharmacyProduct $product): array
    {
        return [
            'id' => $product->id,
            'name_ar' => $product->name_ar,
            'name_en' => $product->name_en,
            'price' => $this->decimal($product->price),
            'requires_prescription' => (bool) $product->requires_prescription,
            'stock_quantity' => (int) $product->stock_quantity,
            'is_active' => (bool) $product->is_active,
        ];
    }

    private function labOrderPayload(LabOrder $order): array
    {
        return [
            'id' => $order->id,
            'number' => $order->order_number,
            'status' => $this->wire($order->order_status),
            'payment_status' => $this->wire($order->payment_status),
            'grand_total' => $this->decimal($order->grand_total),
            'scheduled_at' => $order->scheduled_at?->toISOString(),
            'patient' => $this->patientSummary($order->patient),
            'payment' => $this->paymentSummary($order->payment),
            'items' => $order->items->map(fn ($item): array => [
                'id' => $item->id,
                'item_type' => $this->wire($item->item_type),
                'item_name' => $item->item_name,
                'quantity' => (int) $item->quantity,
                'line_total' => $this->decimal($item->line_total),
            ])->values()->all(),
            'results_count' => $order->results->count(),
        ];
    }

    private function labTestPayload(LabTest $test): array
    {
        return [
            'id' => $test->id,
            'name_ar' => $test->name_ar,
            'name_en' => $test->name_en,
            'code' => $test->code,
            'price' => $this->decimal($test->price),
            'sample_type' => $test->sample_type,
            'result_time_hours' => $test->result_time_hours,
            'is_active' => (bool) $test->is_active,
        ];
    }

    private function labPackagePayload(LabPackage $package): array
    {
        return [
            'id' => $package->id,
            'name_ar' => $package->name_ar,
            'name_en' => $package->name_en,
            'price' => $this->decimal($package->price),
            'is_active' => (bool) $package->is_active,
        ];
    }

    private function gymBookingPayload(GymBooking $booking): array
    {
        return [
            'id' => $booking->id,
            'number' => $booking->booking_number,
            'status' => $this->wire($booking->status),
            'total_amount' => $this->decimal($booking->total_amount),
            'starts_at' => $booking->starts_at?->toISOString(),
            'ends_at' => $booking->ends_at?->toISOString(),
            'patient' => $this->patientSummary($booking->patient),
            'payment' => $this->paymentSummary($booking->payment),
            'plan' => $booking->membershipPlan ? $this->gymPlanPayload($booking->membershipPlan) : null,
            'class' => $booking->gymClass ? $this->gymClassPayload($booking->gymClass) : null,
        ];
    }

    private function gymPlanPayload(GymMembershipPlan $plan): array
    {
        return [
            'id' => $plan->id,
            'name_ar' => $plan->name_ar,
            'name_en' => $plan->name_en,
            'duration_days' => (int) $plan->duration_days,
            'price' => $this->decimal($plan->price),
            'sessions_count' => $plan->sessions_count ? (int) $plan->sessions_count : null,
            'is_active' => (bool) $plan->is_active,
        ];
    }

    private function gymClassPayload(GymClassModel $class): array
    {
        return [
            'id' => $class->id,
            'name_ar' => $class->name_ar,
            'name_en' => $class->name_en,
            'starts_at' => $class->starts_at?->toISOString(),
            'ends_at' => $class->ends_at?->toISOString(),
            'capacity' => $class->capacity ? (int) $class->capacity : null,
            'price' => $this->decimal($class->price),
            'is_active' => (bool) $class->is_active,
        ];
    }

    private function coachBookingPayload(CoachBooking $booking): array
    {
        return [
            'id' => $booking->id,
            'number' => $booking->booking_number,
            'status' => $this->wire($booking->status),
            'total_amount' => $this->decimal($booking->total_amount),
            'patient_goal' => $booking->patient_goal,
            'patient' => $this->patientSummary($booking->patient),
            'payment' => $this->paymentSummary($booking->payment),
            'session_type' => $booking->sessionType ? $this->coachSessionTypePayload($booking->sessionType) : null,
            'slot' => $booking->availabilitySlot ? $this->coachAvailabilityPayload($booking->availabilitySlot) : null,
        ];
    }

    private function coachSessionTypePayload(CoachSessionType $type): array
    {
        return [
            'id' => $type->id,
            'name_ar' => $type->name_ar,
            'name_en' => $type->name_en,
            'duration_minutes' => (int) $type->duration_minutes,
            'price' => $this->decimal($type->price),
            'session_mode' => $this->wire($type->session_mode),
            'is_active' => (bool) $type->is_active,
        ];
    }

    private function coachAvailabilityPayload(CoachAvailabilitySlot $slot): array
    {
        return [
            'id' => $slot->id,
            'starts_at' => $slot->starts_at?->toISOString(),
            'ends_at' => $slot->ends_at?->toISOString(),
            'status' => $this->wire($slot->status),
        ];
    }

    private function coachPackagePayload(CoachPackage $package): array
    {
        return [
            'id' => $package->id,
            'name_ar' => $package->name_ar,
            'name_en' => $package->name_en,
            'sessions_count' => (int) $package->sessions_count,
            'duration_days' => $package->duration_days ? (int) $package->duration_days : null,
            'price' => $this->decimal($package->price),
            'is_active' => (bool) $package->is_active,
        ];
    }

    private function paymentSummary(?Model $payment): ?array
    {
        if (! $payment) {
            return null;
        }

        return [
            'id' => $payment->id,
            'status' => $this->wire($payment->status),
            'amount' => $this->decimal($payment->amount),
            'currency' => $payment->currency,
            'method_type' => $this->wire($payment->paymentMethod?->type),
        ];
    }

    private function patientSummary(?Model $patient): ?array
    {
        if (! $patient) {
            return null;
        }

        return [
            'id' => $patient->id,
            'name' => $patient->name,
        ];
    }

    private function listPayload(array $items): array
    {
        return [
            'items' => $items,
            'meta' => [
                'count' => count($items),
            ],
        ];
    }

    private function wire(mixed $value): ?string
    {
        if ($value instanceof UnitEnum) {
            return $value instanceof \BackedEnum ? (string) $value->value : $value->name;
        }

        return $value === null ? null : (string) $value;
    }

    private function decimal(mixed $value): ?float
    {
        return $value === null ? null : round((float) $value, 2);
    }
}
