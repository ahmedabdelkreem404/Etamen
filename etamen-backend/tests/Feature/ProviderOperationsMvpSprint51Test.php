<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Domain\Enums\ConsultationType;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\Fitness\Domain\Enums\CoachBookingStatus;
use App\Modules\Fitness\Domain\Enums\CoachSessionMode;
use App\Modules\Fitness\Domain\Enums\GymBookingStatus;
use App\Modules\Fitness\Infrastructure\Models\CoachAvailabilitySlot;
use App\Modules\Fitness\Infrastructure\Models\CoachBooking;
use App\Modules\Fitness\Infrastructure\Models\CoachPackage;
use App\Modules\Fitness\Infrastructure\Models\CoachSessionType;
use App\Modules\Fitness\Infrastructure\Models\GymBooking;
use App\Modules\Fitness\Infrastructure\Models\GymClassModel;
use App\Modules\Fitness\Infrastructure\Models\GymMembershipPlan;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Labs\Domain\Enums\LabOrderItemType;
use App\Modules\Labs\Domain\Enums\LabOrderPaymentStatus;
use App\Modules\Labs\Domain\Enums\LabOrderStatus;
use App\Modules\Labs\Domain\Enums\LabSampleCollectionMethod;
use App\Modules\Labs\Infrastructure\Models\LabOrder;
use App\Modules\Labs\Infrastructure\Models\LabPackage;
use App\Modules\Labs\Infrastructure\Models\LabTest;
use App\Modules\Payments\Database\Seeders\PaymentMethodSeeder;
use App\Modules\Payments\Domain\Enums\PaymentMethodType;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;
use App\Modules\Pharmacies\Domain\Enums\PharmacyDeliveryMethod;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderPaymentStatus;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderStatus;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyOrder;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyProduct;
use App\Modules\Providers\Domain\Enums\ProviderPermission;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\DoctorProfile;
use App\Modules\Providers\Infrastructure\Models\HospitalDepartment;
use App\Modules\Providers\Infrastructure\Models\HospitalDoctor;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderStaff;
use App\Modules\Radiology\Domain\Enums\RadiologyOrderStatus;
use App\Modules\Radiology\Infrastructure\Models\RadiologyOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProviderOperationsMvpSprint51Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(PaymentMethodSeeder::class);
    }

    public function test_doctor_owner_can_list_and_confirm_own_appointments(): void
    {
        [$owner, $doctorProvider, $doctorProfile] = $this->doctorProvider();
        $patient = User::factory()->create();
        $appointment = $this->appointment($patient, $doctorProvider, $doctorProfile, AppointmentStatus::Confirmed);

        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/provider/workspace/'.$doctorProvider->id.'/doctor/appointments')
            ->assertOk()
            ->assertJsonPath('data.items.0.id', $appointment->id)
            ->assertJsonMissing(['medical_private', 'storage/private', 'payment_config', 'national_id']);

        $this->postJson('/api/v1/provider/workspace/'.$doctorProvider->id.'/doctor/appointments/'.$appointment->id.'/confirm')
            ->assertOk()
            ->assertJsonPath('data.status', AppointmentStatus::Accepted->value);
    }

    public function test_provider_operation_permission_guards_block_patient_wrong_provider_and_limited_staff_actions(): void
    {
        [$owner, $doctorProvider, $doctorProfile] = $this->doctorProvider();
        [, $otherProvider] = $this->providerWithOwner(ProviderType::Radiology);
        $limitedStaff = $this->addStaff($doctorProvider, [ProviderPermission::ViewAppointments->value]);
        $appointment = $this->appointment(User::factory()->create(), $doctorProvider, $doctorProfile, AppointmentStatus::Confirmed);

        Sanctum::actingAs(User::factory()->create());
        $this->getJson('/api/v1/provider/workspace/'.$doctorProvider->id.'/doctor/appointments')->assertForbidden();

        Sanctum::actingAs($owner);
        $this->getJson('/api/v1/provider/workspace/'.$otherProvider->id.'/radiology/orders')->assertForbidden();

        Sanctum::actingAs($limitedStaff);
        $this->getJson('/api/v1/provider/workspace/'.$doctorProvider->id.'/doctor/appointments')->assertOk();
        $this->postJson('/api/v1/provider/workspace/'.$doctorProvider->id.'/doctor/appointments/'.$appointment->id.'/confirm')->assertForbidden();
    }

    public function test_hospital_radiology_pharmacy_lab_gym_and_coach_operation_lists_are_safe(): void
    {
        [$hospitalOwner, $hospital] = $this->providerWithOwner(ProviderType::Hospital);
        [, $doctorProvider, $doctorProfile] = $this->doctorProvider();
        $patient = User::factory()->create();
        $department = HospitalDepartment::query()->create([
            'hospital_provider_id' => $hospital->id,
            'name_ar' => 'Cardiology',
            'name_en' => 'Cardiology',
            'is_active' => true,
        ]);
        $link = HospitalDoctor::query()->create([
            'hospital_provider_id' => $hospital->id,
            'doctor_provider_id' => $doctorProvider->id,
            'hospital_department_id' => $department->id,
            'consultation_fee' => 400,
            'is_active' => true,
        ]);
        $hospitalAppointment = $this->appointment($patient, $doctorProvider, $doctorProfile, AppointmentStatus::Confirmed, [
            'hospital_provider_id' => $hospital->id,
            'hospital_department_id' => $department->id,
            'hospital_doctor_id' => $link->id,
        ]);

        Sanctum::actingAs($hospitalOwner);
        $this->getJson('/api/v1/provider/workspace/'.$hospital->id.'/hospital/appointments')
            ->assertOk()
            ->assertJsonPath('data.items.0.id', $hospitalAppointment->id);
        $this->getJson('/api/v1/provider/workspace/'.$hospital->id.'/hospital/departments')
            ->assertOk()
            ->assertJsonPath('data.items.0.id', $department->id);
        $this->getJson('/api/v1/provider/workspace/'.$hospital->id.'/hospital/doctors')
            ->assertOk()
            ->assertJsonPath('data.items.0.id', $link->id);

        $this->assertSafeProviderResponse($this->exerciseRadiologyOperations($patient));
        $this->assertSafeProviderResponse($this->exercisePharmacyOperations($patient));
        $this->assertSafeProviderResponse($this->exerciseLabOperations($patient));
        $this->assertSafeProviderResponse($this->exerciseGymOperations($patient));
        $this->assertSafeProviderResponse($this->exerciseCoachOperations($patient));
    }

    public function test_radiology_status_actions_require_manage_permission(): void
    {
        [, $radiology] = $this->providerWithOwner(ProviderType::Radiology);
        $limited = $this->addStaff($radiology, [ProviderPermission::ViewRadiologyOrders->value]);
        $order = $this->radiologyOrder(User::factory()->create(), $radiology, RadiologyOrderStatus::Paid);

        Sanctum::actingAs($limited);
        $this->getJson('/api/v1/provider/workspace/'.$radiology->id.'/radiology/orders')->assertOk();
        $this->postJson('/api/v1/provider/workspace/'.$radiology->id.'/radiology/orders/'.$order->id.'/accept')->assertForbidden();
    }

    public function test_provider_workspace_pharmacy_actions_cover_lifecycle_guards_and_payment_regression(): void
    {
        Storage::fake('medical_private');

        $patient = $this->patientUser();
        [$owner, $provider] = $this->providerWithOwner(ProviderType::Pharmacy);
        $product = PharmacyProduct::query()->create([
            'provider_id' => $provider->id,
            'name_ar' => 'دواء محلي',
            'name_en' => 'Local medicine',
            'price' => 150,
            'stock_quantity' => 10,
            'requires_prescription' => true,
            'is_active' => true,
        ]);
        $order = $this->createPharmacyOrderThroughApi($patient, $provider, $product, withPrescription: true);

        Sanctum::actingAs($owner);
        $acceptContent = $this->postJson('/api/v1/provider/workspace/'.$provider->id.'/pharmacy/orders/'.$order->id.'/accept')
            ->assertOk()
            ->assertJsonPath('data.status', PharmacyOrderStatus::Accepted->value)
            ->content();
        $this->assertSafeProviderResponse($acceptContent);
        $this->assertStringNotContainsString('prescription.pdf', $acceptContent);

        $rejectedOrder = $this->createPharmacyOrderThroughApi($patient, $provider, $product, withPrescription: true);
        Sanctum::actingAs($owner);
        $this->postJson('/api/v1/provider/workspace/'.$provider->id.'/pharmacy/orders/'.$rejectedOrder->id.'/reject')
            ->assertUnprocessable();
        $this->postJson('/api/v1/provider/workspace/'.$provider->id.'/pharmacy/orders/'.$rejectedOrder->id.'/reject', [
            'reason' => 'Local QA rejection reason.',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', PharmacyOrderStatus::Rejected->value);

        [$wrongOwner, $wrongProvider] = $this->providerWithOwner(ProviderType::Pharmacy);
        Sanctum::actingAs($wrongOwner);
        $this->getJson('/api/v1/provider/workspace/'.$wrongProvider->id.'/pharmacy/orders/'.$order->id)->assertNotFound();
        $this->postJson('/api/v1/provider/workspace/'.$wrongProvider->id.'/pharmacy/orders/'.$order->id.'/reject', [
            'reason' => 'Wrong provider attempt.',
        ])->assertNotFound();

        $limited = $this->addStaff($provider, [ProviderPermission::ViewPharmacyOrders->value]);
        Sanctum::actingAs($limited);
        $this->getJson('/api/v1/provider/workspace/'.$provider->id.'/pharmacy/orders')->assertOk();
        $this->postJson('/api/v1/provider/workspace/'.$provider->id.'/pharmacy/orders/'.$order->id.'/reject', [
            'reason' => 'Limited staff attempt.',
        ])->assertForbidden();

        $payment = $this->preparePaymentProofForPharmacyOrder($patient, $order);
        Sanctum::actingAs($this->adminUser());
        $detailsContent = $this->getJson('/api/v1/admin/operations/payments/'.$payment->id)
            ->assertOk()
            ->assertJsonPath('data.proof.exists', true)
            ->content();
        $this->assertSafeProviderResponse($detailsContent);

        $this->postJson('/api/v1/admin/operations/payments/'.$payment->id.'/accept')
            ->assertOk()
            ->assertJsonPath('data.status', PaymentStatus::Verified->value)
            ->assertJsonPath('data.payable.status', PharmacyOrderStatus::Paid->value);
        $this->assertSame(PharmacyOrderPaymentStatus::Paid, $order->refresh()->payment_status);

        Sanctum::actingAs($owner);
        $this->postJson('/api/v1/provider/workspace/'.$provider->id.'/pharmacy/orders/'.$order->id.'/preparing')
            ->assertOk()
            ->assertJsonPath('data.status', PharmacyOrderStatus::Preparing->value);
        $this->postJson('/api/v1/provider/workspace/'.$provider->id.'/pharmacy/orders/'.$order->id.'/ready')
            ->assertOk()
            ->assertJsonPath('data.status', PharmacyOrderStatus::ReadyForPickup->value);
        $this->postJson('/api/v1/provider/workspace/'.$provider->id.'/pharmacy/orders/'.$order->id.'/out-for-delivery')
            ->assertOk()
            ->assertJsonPath('data.status', PharmacyOrderStatus::OutForDelivery->value);
        $this->postJson('/api/v1/provider/workspace/'.$provider->id.'/pharmacy/orders/'.$order->id.'/complete')
            ->assertOk()
            ->assertJsonPath('data.status', PharmacyOrderStatus::Delivered->value);
    }

    public function test_provider_workspace_lab_actions_cover_lifecycle_guards_and_payment_regression(): void
    {
        Storage::fake('medical_private');

        $patient = $this->patientUser('lab-patient-'.Str::random(6).'@example.com');
        [$owner, $provider] = $this->providerWithOwner(ProviderType::Lab);
        $test = LabTest::query()->create([
            'provider_id' => $provider->id,
            'name_ar' => 'صورة دم',
            'name_en' => 'CBC',
            'code' => 'CBC-'.Str::upper(Str::random(4)),
            'price' => 180,
            'sample_type' => 'blood',
            'is_active' => true,
        ]);
        $order = $this->createLabOrderThroughApi($patient, $provider, $test);

        Sanctum::actingAs($owner);
        $acceptContent = $this->postJson('/api/v1/provider/workspace/'.$provider->id.'/lab/orders/'.$order->id.'/accept')
            ->assertOk()
            ->assertJsonPath('data.status', LabOrderStatus::Accepted->value)
            ->content();
        $this->assertSafeProviderResponse($acceptContent);
        $this->assertStringNotContainsString('diagnosis', $acceptContent);
        $this->assertStringNotContainsString('interpretation', $acceptContent);

        $rejectedOrder = $this->createLabOrderThroughApi($patient, $provider, $test);
        Sanctum::actingAs($owner);
        $this->postJson('/api/v1/provider/workspace/'.$provider->id.'/lab/orders/'.$rejectedOrder->id.'/reject')
            ->assertUnprocessable();
        $this->postJson('/api/v1/provider/workspace/'.$provider->id.'/lab/orders/'.$rejectedOrder->id.'/reject', [
            'reason' => 'Local QA rejection reason.',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', LabOrderStatus::Rejected->value);

        [$wrongOwner, $wrongProvider] = $this->providerWithOwner(ProviderType::Lab);
        Sanctum::actingAs($wrongOwner);
        $this->getJson('/api/v1/provider/workspace/'.$wrongProvider->id.'/lab/orders/'.$order->id)->assertNotFound();
        $this->postJson('/api/v1/provider/workspace/'.$wrongProvider->id.'/lab/orders/'.$order->id.'/reject', [
            'reason' => 'Wrong provider attempt.',
        ])->assertNotFound();

        $limited = $this->addStaff($provider, [ProviderPermission::ViewLabOrders->value]);
        Sanctum::actingAs($limited);
        $this->getJson('/api/v1/provider/workspace/'.$provider->id.'/lab/orders')->assertOk();
        $this->postJson('/api/v1/provider/workspace/'.$provider->id.'/lab/orders/'.$order->id.'/reject', [
            'reason' => 'Limited staff attempt.',
        ])->assertForbidden();

        $payment = $this->preparePaymentProofForLabOrder($patient, $order);
        Sanctum::actingAs($this->adminUser());
        $detailsContent = $this->getJson('/api/v1/admin/operations/payments/'.$payment->id)
            ->assertOk()
            ->assertJsonPath('data.proof.exists', true)
            ->content();
        $this->assertSafeProviderResponse($detailsContent);

        $this->postJson('/api/v1/admin/operations/payments/'.$payment->id.'/accept')
            ->assertOk()
            ->assertJsonPath('data.status', PaymentStatus::Verified->value)
            ->assertJsonPath('data.payable.status', LabOrderStatus::Paid->value);
        $this->assertSame(LabOrderPaymentStatus::Paid, $order->refresh()->payment_status);

        Sanctum::actingAs($owner);
        $this->postJson('/api/v1/provider/workspace/'.$provider->id.'/lab/orders/'.$order->id.'/sample-scheduled')
            ->assertOk()
            ->assertJsonPath('data.status', LabOrderStatus::SampleScheduled->value);
        $this->postJson('/api/v1/provider/workspace/'.$provider->id.'/lab/orders/'.$order->id.'/sample-collected')
            ->assertOk()
            ->assertJsonPath('data.status', LabOrderStatus::SampleCollected->value);
        $this->postJson('/api/v1/provider/workspace/'.$provider->id.'/lab/orders/'.$order->id.'/processing')
            ->assertOk()
            ->assertJsonPath('data.status', LabOrderStatus::Processing->value);
        $this->postJson('/api/v1/provider/workspace/'.$provider->id.'/lab/orders/'.$order->id.'/result-ready')
            ->assertOk()
            ->assertJsonPath('data.status', LabOrderStatus::ResultReady->value);
        $this->postJson('/api/v1/provider/workspace/'.$provider->id.'/lab/orders/'.$order->id.'/complete')
            ->assertOk()
            ->assertJsonPath('data.status', LabOrderStatus::Completed->value);
    }

    private function exerciseRadiologyOperations(User $patient): string
    {
        [$owner, $provider] = $this->providerWithOwner(ProviderType::Radiology);
        $order = $this->radiologyOrder($patient, $provider, RadiologyOrderStatus::Paid);

        Sanctum::actingAs($owner);

        $list = $this->getJson('/api/v1/provider/workspace/'.$provider->id.'/radiology/orders')
            ->assertOk()
            ->assertJsonPath('data.items.0.id', $order->id)
            ->content();

        $this->postJson('/api/v1/provider/workspace/'.$provider->id.'/radiology/orders/'.$order->id.'/accept')
            ->assertOk()
            ->assertJsonPath('data.status', RadiologyOrderStatus::Accepted->value);

        return $list;
    }

    private function exercisePharmacyOperations(User $patient): string
    {
        [$owner, $provider] = $this->providerWithOwner(ProviderType::Pharmacy);
        $product = PharmacyProduct::query()->create([
            'provider_id' => $provider->id,
            'name_ar' => 'Demo product',
            'name_en' => 'Demo product',
            'price' => 25,
            'stock_quantity' => 10,
            'requires_prescription' => false,
            'is_active' => true,
        ]);
        $order = PharmacyOrder::query()->create([
            'order_number' => 'PH-'.Str::upper(Str::random(8)),
            'patient_user_id' => $patient->id,
            'pharmacy_provider_id' => $provider->id,
            'subtotal' => 25,
            'discount_total' => 0,
            'commission_amount' => 0,
            'provider_net_amount' => 25,
            'grand_total' => 25,
            'currency' => 'EGP',
            'payment_status' => PharmacyOrderPaymentStatus::Unpaid,
            'order_status' => PharmacyOrderStatus::Pending,
            'delivery_method' => PharmacyDeliveryMethod::Pickup,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => 'Demo product',
            'unit_price' => 25,
            'quantity' => 1,
            'line_total' => 25,
        ]);

        Sanctum::actingAs($owner);

        $orders = $this->getJson('/api/v1/provider/workspace/'.$provider->id.'/pharmacy/orders')
            ->assertOk()
            ->assertJsonPath('data.items.0.id', $order->id)
            ->content();
        $this->getJson('/api/v1/provider/workspace/'.$provider->id.'/pharmacy/products')
            ->assertOk()
            ->assertJsonPath('data.items.0.id', $product->id);

        return $orders;
    }

    private function exerciseLabOperations(User $patient): string
    {
        [$owner, $provider] = $this->providerWithOwner(ProviderType::Lab);
        $test = LabTest::query()->create([
            'provider_id' => $provider->id,
            'name_ar' => 'CBC',
            'name_en' => 'CBC',
            'code' => 'CBC',
            'price' => 120,
            'sample_type' => 'blood',
            'is_active' => true,
        ]);
        LabPackage::query()->create([
            'provider_id' => $provider->id,
            'name_ar' => 'Basic package',
            'name_en' => 'Basic package',
            'price' => 200,
            'is_active' => true,
        ]);
        $order = LabOrder::query()->create([
            'order_number' => 'LAB-'.Str::upper(Str::random(8)),
            'patient_user_id' => $patient->id,
            'lab_provider_id' => $provider->id,
            'subtotal' => 120,
            'discount_total' => 0,
            'commission_amount' => 0,
            'provider_net_amount' => 120,
            'grand_total' => 120,
            'currency' => 'EGP',
            'payment_status' => LabOrderPaymentStatus::Unpaid,
            'order_status' => LabOrderStatus::LabReview,
            'sample_collection_method' => LabSampleCollectionMethod::BranchVisit,
        ]);
        $order->items()->create([
            'item_type' => 'test',
            'test_id' => $test->id,
            'item_name' => 'CBC',
            'unit_price' => 120,
            'quantity' => 1,
            'line_total' => 120,
        ]);

        Sanctum::actingAs($owner);

        $orders = $this->getJson('/api/v1/provider/workspace/'.$provider->id.'/lab/orders')
            ->assertOk()
            ->assertJsonPath('data.items.0.id', $order->id)
            ->content();
        $this->getJson('/api/v1/provider/workspace/'.$provider->id.'/lab/catalog')
            ->assertOk()
            ->assertJsonPath('data.tests.0.id', $test->id);

        return $orders;
    }

    private function exerciseGymOperations(User $patient): string
    {
        [$owner, $provider] = $this->providerWithOwner(ProviderType::Gym);
        $plan = GymMembershipPlan::query()->create([
            'provider_id' => $provider->id,
            'name_ar' => 'Monthly',
            'duration_days' => 30,
            'price' => 500,
            'is_active' => true,
        ]);
        GymClassModel::query()->create([
            'provider_id' => $provider->id,
            'name_ar' => 'Cardio',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
            'price' => 150,
            'is_active' => true,
        ]);
        $booking = GymBooking::query()->create([
            'booking_number' => 'GYM-'.Str::upper(Str::random(8)),
            'patient_user_id' => $patient->id,
            'provider_id' => $provider->id,
            'membership_plan_id' => $plan->id,
            'status' => GymBookingStatus::Paid,
            'total_amount' => 500,
        ]);

        Sanctum::actingAs($owner);

        $bookings = $this->getJson('/api/v1/provider/workspace/'.$provider->id.'/gym/bookings')
            ->assertOk()
            ->assertJsonPath('data.items.0.id', $booking->id)
            ->content();
        $this->getJson('/api/v1/provider/workspace/'.$provider->id.'/gym/plans')->assertOk();
        $this->getJson('/api/v1/provider/workspace/'.$provider->id.'/gym/classes')->assertOk();

        $this->postJson('/api/v1/provider/workspace/'.$provider->id.'/gym/bookings/'.$booking->id.'/confirm')
            ->assertOk()
            ->assertJsonPath('data.status', GymBookingStatus::Confirmed->value);

        return $bookings;
    }

    private function exerciseCoachOperations(User $patient): string
    {
        [$owner, $provider] = $this->providerWithOwner(ProviderType::FitnessCoach);
        $sessionType = CoachSessionType::query()->create([
            'provider_id' => $provider->id,
            'name_ar' => 'Assessment',
            'duration_minutes' => 45,
            'price' => 250,
            'session_mode' => CoachSessionMode::Online,
            'is_active' => true,
        ]);
        $slot = CoachAvailabilitySlot::query()->create([
            'provider_id' => $provider->id,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
            'status' => 'booked',
        ]);
        CoachPackage::query()->create([
            'provider_id' => $provider->id,
            'name_ar' => 'Followup package',
            'sessions_count' => 4,
            'price' => 800,
            'is_active' => true,
        ]);
        $booking = CoachBooking::query()->create([
            'booking_number' => 'COACH-'.Str::upper(Str::random(8)),
            'patient_user_id' => $patient->id,
            'coach_provider_id' => $provider->id,
            'session_type_id' => $sessionType->id,
            'availability_slot_id' => $slot->id,
            'status' => CoachBookingStatus::Paid,
            'total_amount' => 250,
            'patient_goal' => 'Safe fitness goal.',
        ]);

        Sanctum::actingAs($owner);

        $bookings = $this->getJson('/api/v1/provider/workspace/'.$provider->id.'/coach/bookings')
            ->assertOk()
            ->assertJsonPath('data.items.0.id', $booking->id)
            ->content();
        $this->getJson('/api/v1/provider/workspace/'.$provider->id.'/coach/availability')->assertOk();
        $this->getJson('/api/v1/provider/workspace/'.$provider->id.'/coach/session-types')->assertOk();
        $this->getJson('/api/v1/provider/workspace/'.$provider->id.'/coach/packages')->assertOk();

        $this->postJson('/api/v1/provider/workspace/'.$provider->id.'/coach/bookings/'.$booking->id.'/confirm')
            ->assertOk()
            ->assertJsonPath('data.status', CoachBookingStatus::Confirmed->value);

        return $bookings;
    }

    private function createPharmacyOrderThroughApi(User $patient, Provider $provider, PharmacyProduct $product, bool $withPrescription = false): PharmacyOrder
    {
        Sanctum::actingAs($patient);
        $prescriptionId = null;

        if ($withPrescription) {
            $prescriptionId = $this->post('/api/v1/pharmacy/prescriptions', [
                'pharmacy_provider_id' => $provider->id,
                'file' => UploadedFile::fake()->create('prescription.pdf', 60, 'application/pdf'),
                'notes' => 'Local QA prescription metadata only.',
            ])
                ->assertCreated()
                ->assertJsonMissingPath('data.file.path')
                ->assertJsonMissingPath('data.file.url')
                ->json('data.id');
        }

        $orderId = $this->postJson('/api/v1/pharmacy/orders', [
            'pharmacy_provider_id' => $provider->id,
            'prescription_id' => $prescriptionId,
            'delivery_method' => PharmacyDeliveryMethod::Pickup->value,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])
            ->assertCreated()
            ->assertJsonPath('data.grand_total', '150.00')
            ->json('data.id');

        return PharmacyOrder::query()->with(['items', 'payment'])->findOrFail($orderId);
    }

    private function createLabOrderThroughApi(User $patient, Provider $provider, LabTest $test): LabOrder
    {
        Sanctum::actingAs($patient);

        $orderId = $this->postJson('/api/v1/lab/orders', [
            'lab_provider_id' => $provider->id,
            'sample_collection_method' => LabSampleCollectionMethod::BranchVisit->value,
            'scheduled_at' => now()->addDay()->toISOString(),
            'items' => [
                [
                    'item_type' => LabOrderItemType::Test->value,
                    'test_id' => $test->id,
                    'quantity' => 1,
                ],
            ],
        ])
            ->assertCreated()
            ->assertJsonPath('data.grand_total', '180.00')
            ->json('data.id');

        return LabOrder::query()->with(['items', 'payment'])->findOrFail($orderId);
    }

    private function preparePaymentProofForPharmacyOrder(User $patient, PharmacyOrder $order): Payment
    {
        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/pharmacy/orders/'.$order->id.'/pay')
            ->assertOk()
            ->assertJsonPath('data.payment_status', PharmacyOrderPaymentStatus::PendingPayment->value);

        return $this->uploadManualProof($patient, $order->refresh()->payment);
    }

    private function preparePaymentProofForLabOrder(User $patient, LabOrder $order): Payment
    {
        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/lab/orders/'.$order->id.'/pay')
            ->assertOk()
            ->assertJsonPath('data.payment_status', LabOrderPaymentStatus::PendingPayment->value);

        return $this->uploadManualProof($patient, $order->refresh()->payment);
    }

    private function uploadManualProof(User $patient, Payment $payment): Payment
    {
        $method = $this->paymentMethod(PaymentMethodType::ManualVodafoneCash);

        Sanctum::actingAs($patient);
        $this->postJson('/api/v1/payments/'.$payment->id.'/manual/select', [
            'payment_method_id' => $method->id,
        ])->assertOk();

        $this->post('/api/v1/payments/'.$payment->id.'/proofs', [
            'file' => UploadedFile::fake()->create('proof.jpg', 30, 'image/jpeg'),
            'reference_number' => 'VC-'.Str::upper(Str::random(6)),
            'sender_phone' => '01012345678',
        ])
            ->assertCreated()
            ->assertJsonMissingPath('data.file.path')
            ->assertJsonMissingPath('data.file.url');

        return $payment->refresh();
    }

    private function paymentMethod(PaymentMethodType $type): PaymentMethod
    {
        $method = PaymentMethod::query()->where('type', $type)->firstOrFail();
        $method->update(['is_active' => true]);

        return $method->refresh();
    }

    private function patientUser(string $email = 'patient-qa@example.com'): User
    {
        $user = User::factory()->create(['email' => $email]);
        $user->assignRole(UserRole::Patient->value);

        return $user;
    }

    private function adminUser(): User
    {
        $user = User::factory()->create(['email' => 'admin-'.Str::random(8).'@example.com']);
        $user->assignRole(UserRole::SuperAdmin->value);

        return $user;
    }

    private function providerWithOwner(ProviderType $type): array
    {
        $owner = User::factory()->create();
        $provider = Provider::query()->create([
            'type' => $type,
            'owner_user_id' => $owner->id,
            'name_ar' => $type->value.' demo',
            'name_en' => ucfirst(str_replace('_', ' ', $type->value)).' Demo',
            'status' => ProviderStatus::Approved,
            'is_active' => true,
            'approved_at' => now(),
        ]);

        ProviderStaff::query()->create([
            'provider_id' => $provider->id,
            'user_id' => $owner->id,
            'role' => ProviderStaffRole::Owner,
            'is_owner' => true,
            'status' => 'active',
        ]);

        return [$owner, $provider];
    }

    private function doctorProvider(): array
    {
        [$owner, $provider] = $this->providerWithOwner(ProviderType::Doctor);
        $profile = DoctorProfile::query()->create([
            'provider_id' => $provider->id,
            'user_id' => $owner->id,
            'consultation_fee' => 300,
        ]);

        return [$owner, $provider, $profile];
    }

    private function addStaff(Provider $provider, array $permissions): User
    {
        $user = User::factory()->create();
        ProviderStaff::query()->create([
            'provider_id' => $provider->id,
            'user_id' => $user->id,
            'role' => ProviderStaffRole::Staff,
            'is_owner' => false,
            'status' => 'active',
            'permissions' => $permissions,
        ]);

        return $user;
    }

    private function appointment(User $patient, Provider $doctorProvider, DoctorProfile $profile, AppointmentStatus $status, array $extra = []): Appointment
    {
        return Appointment::query()->create([
            'appointment_number' => 'APT-'.Str::upper(Str::random(8)),
            'patient_user_id' => $patient->id,
            'doctor_profile_id' => $profile->id,
            'provider_id' => $doctorProvider->id,
            'consultation_type' => ConsultationType::Clinic,
            'price' => 300,
            'currency' => 'EGP',
            'status' => $status,
            'booked_at' => now(),
            'confirmed_at' => $status === AppointmentStatus::Confirmed ? now() : null,
            ...$extra,
        ]);
    }

    private function radiologyOrder(User $patient, Provider $provider, RadiologyOrderStatus $status): RadiologyOrder
    {
        return RadiologyOrder::query()->create([
            'order_number' => 'RAD-'.Str::upper(Str::random(8)),
            'patient_user_id' => $patient->id,
            'provider_id' => $provider->id,
            'status' => $status,
            'subtotal' => 350,
            'discount_amount' => 0,
            'total_amount' => 350,
        ]);
    }

    private function assertSafeProviderResponse(string $content): void
    {
        $this->assertStringNotContainsString('medical_private', $content);
        $this->assertStringNotContainsString('storage/private', $content);
        $this->assertStringNotContainsString('raw_path', $content);
        $this->assertStringNotContainsString('payment_config', $content);
        $this->assertStringNotContainsString('national_id', $content);
        $this->assertStringNotContainsString('contract_terms', $content);
    }
}
