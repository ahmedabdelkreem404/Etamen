<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Appointments\Domain\Enums\AppointmentSlotStatus;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Domain\Enums\ConsultationType;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\Appointments\Infrastructure\Models\AppointmentReview;
use App\Modules\Appointments\Infrastructure\Models\AppointmentSlot;
use App\Modules\Appointments\Infrastructure\Models\DoctorSchedule;
use App\Modules\Appointments\Infrastructure\Models\DoctorScheduleDay;
use App\Modules\CarePlans\Domain\Enums\CarePlanFoodCategory;
use App\Modules\CarePlans\Domain\Enums\CarePlanInstructionType;
use App\Modules\CarePlans\Domain\Enums\CarePlanMealType;
use App\Modules\CarePlans\Domain\Enums\CarePlanSource;
use App\Modules\CarePlans\Domain\Enums\CarePlanStatus;
use App\Modules\CarePlans\Domain\Enums\CarePlanType;
use App\Modules\CarePlans\Domain\Enums\CarePlanVisibility;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanDay;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanFoodItem;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanInstruction;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanMeal;
use App\Modules\AdminOperations\Infrastructure\Models\Dispute;
use App\Modules\AdminOperations\Infrastructure\Models\RefundRequest;
use App\Modules\AdminOperations\Infrastructure\Models\SupportTicket;
use App\Modules\Fitness\Domain\Enums\CoachAvailabilityStatus;
use App\Modules\Fitness\Domain\Enums\CoachSessionMode;
use App\Modules\Fitness\Infrastructure\Models\CoachAvailabilitySlot;
use App\Modules\Fitness\Infrastructure\Models\CoachPackage;
use App\Modules\Fitness\Infrastructure\Models\CoachSessionType;
use App\Modules\Fitness\Infrastructure\Models\GymClassModel;
use App\Modules\Fitness\Infrastructure\Models\GymMembershipPlan;
use App\Modules\Health\Domain\Enums\BloodType;
use App\Modules\Health\Domain\Enums\Gender;
use App\Modules\Health\Domain\Enums\VitalFlag;
use App\Modules\Health\Domain\Enums\VitalSource;
use App\Modules\Health\Domain\Enums\VitalType;
use App\Modules\Health\Infrastructure\Models\HealthProfile;
use App\Modules\Health\Infrastructure\Models\VitalRecord;
use App\Modules\Identity\Database\Seeders\RoleSeeder;
use App\Modules\Identity\Database\Seeders\SuperAdminSeeder;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Labs\Domain\Enums\LabOrderItemType;
use App\Modules\Labs\Domain\Enums\LabOrderPaymentStatus;
use App\Modules\Labs\Domain\Enums\LabOrderStatus;
use App\Modules\Labs\Domain\Enums\LabResultStatus;
use App\Modules\Labs\Domain\Enums\LabSampleCollectionMethod;
use App\Modules\Labs\Infrastructure\Models\LabOrder;
use App\Modules\Labs\Infrastructure\Models\LabOrderItem;
use App\Modules\Labs\Infrastructure\Models\LabPackage;
use App\Modules\Labs\Infrastructure\Models\LabResult;
use App\Modules\Labs\Infrastructure\Models\LabTest;
use App\Modules\Locations\Infrastructure\Models\Area;
use App\Modules\Locations\Infrastructure\Models\City;
use App\Modules\MedicalFiles\Domain\Enums\FileCategory;
use App\Modules\MedicalFiles\Domain\Enums\FileVisibility;
use App\Modules\MedicalFiles\Infrastructure\Models\UploadedFile;
use App\Modules\Medications\Domain\Enums\MedicationFrequencyType;
use App\Modules\Medications\Domain\Enums\MedicationReminderSource;
use App\Modules\Medications\Domain\Enums\MedicationReminderStatus;
use App\Modules\Medications\Infrastructure\Models\MedicationReminder;
use App\Modules\Medications\Infrastructure\Models\MedicationReminderTime;
use App\Modules\Notifications\Database\Seeders\NotificationTemplateSeeder;
use App\Modules\Notifications\Domain\Enums\NotificationCategory;
use App\Modules\Notifications\Domain\Enums\NotificationPriority;
use App\Modules\Notifications\Infrastructure\Models\Notification;
use App\Modules\Payments\Database\Seeders\PaymentMethodSeeder;
use App\Modules\Payments\Domain\Enums\PaymentMethodType;
use App\Modules\Payments\Domain\Enums\PaymentProofStatus;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;
use App\Modules\Payments\Infrastructure\Models\PaymentProof;
use App\Modules\Pharmacies\Domain\Enums\PharmacyDeliveryMethod;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderPaymentStatus;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderStatus;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyOrder;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyOrderItem;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyProduct;
use App\Modules\Providers\Domain\Enums\ApprovalRequestStatus;
use App\Modules\Providers\Domain\Enums\CoachType;
use App\Modules\Providers\Domain\Enums\ProviderPermission;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\DoctorProfile;
use App\Modules\Providers\Infrastructure\Models\CoachProfile;
use App\Modules\Providers\Infrastructure\Models\GymProfile;
use App\Modules\Providers\Infrastructure\Models\HospitalDepartment;
use App\Modules\Providers\Infrastructure\Models\HospitalDoctor;
use App\Modules\Providers\Infrastructure\Models\HospitalProfile;
use App\Modules\Providers\Infrastructure\Models\LabProfile;
use App\Modules\Providers\Infrastructure\Models\PharmacyProfile;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderApprovalRequest;
use App\Modules\Providers\Infrastructure\Models\ProviderBookingSetting;
use App\Modules\Providers\Infrastructure\Models\ProviderBranch;
use App\Modules\Providers\Infrastructure\Models\ProviderStaff;
use App\Modules\Providers\Infrastructure\Models\RadiologyProfile;
use App\Modules\Providers\Infrastructure\Models\Specialty;
use App\Modules\Radiology\Database\Seeders\RadiologyScanCategorySeeder;
use App\Modules\Radiology\Infrastructure\Models\RadiologyPreparationInstruction;
use App\Modules\Radiology\Infrastructure\Models\RadiologyScan;
use App\Modules\Radiology\Infrastructure\Models\RadiologyScanCategory;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PilotDemoSeeder extends Seeder
{
    private const DEMO_PASSWORD = 'Password1234';

    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('PilotDemoSeeder is local/staging/testing only and must not run in production.');
        }

        DB::transaction(function (): void {
            $this->call([
                RoleSeeder::class,
                PaymentMethodSeeder::class,
                RadiologyScanCategorySeeder::class,
                NotificationTemplateSeeder::class,
                SuperAdminSeeder::class,
            ]);

            $admin = $this->demoUser(
                env('PILOT_ADMIN_EMAIL', 'pilot.admin@example.test'),
                'Pilot Demo Admin',
                UserRole::Admin,
            );
            $patient = $this->demoUser('pilot.patient@example.test', 'Pilot Patient', UserRole::Patient);
            $doctorUser = $this->demoUser('pilot.doctor@example.test', 'Pilot Doctor', UserRole::Doctor);
            $pharmacyUser = $this->demoUser('pilot.pharmacy@example.test', 'Pilot Pharmacy Admin', UserRole::PharmacyAdmin);
            $labUser = $this->demoUser('pilot.lab@example.test', 'Pilot Lab Admin', UserRole::LabAdmin);
            $radiologyUser = $this->demoUser('pilot.radiology@example.test', 'Pilot Radiology Admin', UserRole::ProviderAdmin);
            $hospitalUser = $this->demoUser('pilot.hospital@example.test', 'Pilot Hospital Admin', UserRole::ProviderAdmin);
            $gymUser = $this->demoUser('pilot.gym@example.test', 'Pilot Gym Admin', UserRole::ProviderAdmin);
            $fitnessCoachUser = $this->demoUser('pilot.fitness.coach@example.test', 'Pilot Fitness Coach', UserRole::ProviderAdmin);
            $nutritionCoachUser = $this->demoUser('pilot.nutrition.coach@example.test', 'Pilot Nutrition Coach', UserRole::ProviderAdmin);
            $limitedStaffUser = $this->demoUser('pilot.provider.staff@example.test', 'Pilot Limited Provider Staff', UserRole::ProviderAdmin);
            $qaPatient = null;
            $qaProviderUser = null;
            if (app()->environment(['local', 'testing'])) {
                $this->demoUser('a@b.co', 'QA Admin', UserRole::Admin);
                $qaPatient = $this->demoUser('p@b.co', 'QA Patient', UserRole::Patient);
                $qaProviderUser = $this->demoUser('d@b.co', 'QA Provider Staff', UserRole::ProviderAdmin);
            }

            $this->seedPatientProfile($patient);
            if ($qaPatient) {
                $this->seedPatientProfile($qaPatient);
            }
            [$city, $area] = $this->seedLocation();
            [$doctorProvider, $doctorProfile, $branch] = $this->seedDoctor($doctorUser, $admin, $city, $area);
            if ($qaProviderUser) {
                $this->providerStaff($doctorProvider, $qaProviderUser, ProviderStaffRole::Owner);
            }
            $this->providerStaff($doctorProvider, $limitedStaffUser, ProviderStaffRole::Staff, [
                ProviderPermission::ViewAppointments->value,
                ProviderPermission::ViewBookings->value,
                ProviderPermission::ViewPayments->value,
            ]);
            $this->seedDoctorScheduleAndSlots($doctorProvider, $doctorProfile, $branch);
            $this->seedDoctorReviews($patient, $doctorProvider, $doctorProfile, $branch);
            $this->seedPaymentMethods();
            $pharmacyProvider = $this->seedPharmacy($pharmacyUser, $admin, $city, $area);
            $labProvider = $this->seedLab($labUser, $admin, $city, $area);
            $this->providerStaff($pharmacyProvider, $limitedStaffUser, ProviderStaffRole::Staff, [
                ProviderPermission::ViewPharmacyOrders->value,
                ProviderPermission::ViewPayments->value,
            ]);
            $this->providerStaff($labProvider, $limitedStaffUser, ProviderStaffRole::Staff, [
                ProviderPermission::ViewLabOrders->value,
                ProviderPermission::ViewPayments->value,
            ]);
            if ($qaProviderUser) {
                $this->providerStaff($pharmacyProvider, $qaProviderUser, ProviderStaffRole::Owner);
                $this->providerStaff($labProvider, $qaProviderUser, ProviderStaffRole::Owner);
            }
            $this->seedRadiology($radiologyUser, $admin, $city, $area);
            $this->seedFitness($gymUser, $fitnessCoachUser, $nutritionCoachUser, $admin, $city, $area);
            $this->seedHealthData($patient);
            $this->seedMedicationData($patient);
            $this->seedCarePlan($patient, $admin, $doctorProvider);
            $this->seedNotification($patient);
            $this->seedDemoLabResultOrder($patient, $labUser, $labProvider);
            $this->seedPharmacyLabHistoryStates($patient, $pharmacyProvider, $labProvider);
            if ($qaPatient) {
                $this->seedPharmacyLabHistoryStates($qaPatient, $pharmacyProvider, $labProvider, 'QA-');
            }
            $this->seedExpandedDemoCatalog($admin, $city, $area);
            $this->seedHospital($hospitalUser, $admin, $city, $area);
            $this->seedAdminOperationsDemo($admin, $patient, $doctorProvider, $pharmacyProvider, $labProvider);
        });
    }

    private function demoUser(string $email, string $name, UserRole $role): User
    {
        $user = User::query()->firstOrNew(['email' => $email]);
        $user->forceFill([
            'name' => $name,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Hash::make(self::DEMO_PASSWORD),
        ])->save();

        $user->syncRoles([$role->value]);

        return $user->refresh();
    }

    private function seedPatientProfile(User $patient): void
    {
        $patient->patientProfile()->updateOrCreate(
            ['user_id' => $patient->id],
            [
                'phone' => '01000000001',
                'date_of_birth' => '1990-01-01',
                'gender' => Gender::Male->value,
                'metadata' => ['pilot_demo' => true],
            ],
        );
    }

    private function seedLocation(): array
    {
        $city = City::query()->updateOrCreate(
            ['slug' => 'cairo-demo'],
            [
                'name_ar' => 'القاهرة',
                'name_en' => 'Cairo',
                'is_active' => true,
            ],
        );

        $area = Area::query()->updateOrCreate(
            ['city_id' => $city->id, 'slug' => 'nasr-city-demo'],
            [
                'name_ar' => 'مدينة نصر',
                'name_en' => 'Nasr City',
                'is_active' => true,
            ],
        );

        return [$city, $area];
    }

    private function seedDoctor(User $doctorUser, User $admin, City $city, Area $area): array
    {
        $specialty = Specialty::query()->updateOrCreate(
            ['slug' => 'cardiology-demo'],
            [
                'name_ar' => 'قلب وأوعية دموية',
                'name_en' => 'Cardiology',
                'is_active' => true,
            ],
        );

        $provider = $this->provider(
            type: ProviderType::Doctor,
            owner: $doctorUser,
            admin: $admin,
            slug: 'pilot-demo-doctor',
            nameAr: 'د. أحمد التجريبي',
            nameEn: 'Dr Ahmed Demo',
            phone: '01000000002',
            descriptionAr: 'طبيب تجريبي لاختبار حجز المواعيد داخل بيئة محلية أو staging.',
            descriptionEn: 'Demo doctor for local/staging appointment walkthroughs.',
        );

        $this->providerStaff($provider, $doctorUser, ProviderStaffRole::Owner);

        $doctorProfile = DoctorProfile::query()->updateOrCreate(
            ['provider_id' => $provider->id],
            [
                'user_id' => $doctorUser->id,
                'title' => 'استشاري',
                'bio_ar' => 'ملف تجريبي آمن لاختبار تجربة حجز طبيب. لا يمثل طبيبًا حقيقيًا.',
                'bio_en' => 'Safe demo profile for doctor booking testing.',
                'avatar_path' => 'legacy-doctorfinder/demo-doctor-avatar-1.png',
                'consultation_fee' => 300,
                'years_of_experience' => 8,
            ],
        );
        $doctorProfile->specialties()->syncWithoutDetaching([$specialty->id]);

        $branch = ProviderBranch::query()->updateOrCreate(
            ['provider_id' => $provider->id, 'name_en' => 'Pilot Nasr City Clinic'],
            [
                'city_id' => $city->id,
                'area_id' => $area->id,
                'name_ar' => 'عيادة مدينة نصر التجريبية',
                'phone' => '01000000002',
                'address_ar' => 'عنوان تجريبي في مدينة نصر لاختبار التطبيق.',
                'address_en' => 'Demo address in Nasr City for app testing.',
                'is_main' => true,
                'is_active' => true,
            ],
        );

        return [$provider, $doctorProfile, $branch];
    }

    private function seedDoctorReviews(User $patient, Provider $provider, DoctorProfile $doctorProfile, ProviderBranch $branch): void
    {
        $reviewRows = [
            [
                'number' => 'APT-PILOT-REVIEW-'.$doctorProfile->id.'-1',
                'email' => $patient->email,
                'name' => $patient->name,
                'rating' => 5,
                'comment' => 'Demo visible review for rating summary only.',
            ],
            [
                'number' => 'APT-PILOT-REVIEW-'.$doctorProfile->id.'-2',
                'email' => 'demo.review.patient.1@example.test',
                'name' => 'Demo Review Patient One',
                'rating' => 4,
                'comment' => 'Second demo visible review for rating summary only.',
            ],
            [
                'number' => 'APT-PILOT-REVIEW-'.$doctorProfile->id.'-3',
                'email' => 'demo.review.patient.2@example.test',
                'name' => 'Demo Review Patient Two',
                'rating' => 5,
                'comment' => 'Third demo visible review for rating summary only.',
            ],
        ];

        foreach ($reviewRows as $index => $row) {
            $reviewPatient = $row['email'] === $patient->email
                ? $patient
                : $this->demoUser($row['email'], $row['name'], UserRole::Patient);

            $appointment = Appointment::query()->updateOrCreate(
                ['appointment_number' => $row['number']],
                [
                    'patient_user_id' => $reviewPatient->id,
                    'doctor_profile_id' => $doctorProfile->id,
                    'provider_id' => $provider->id,
                    'branch_id' => $branch->id,
                    'appointment_slot_id' => null,
                    'consultation_type' => ConsultationType::Clinic,
                    'problem_description' => 'Demo completed appointment for visible rating summary only.',
                    'price' => $doctorProfile->consultation_fee ?? 0,
                    'currency' => 'EGP',
                    'status' => AppointmentStatus::Completed,
                    'payment_id' => null,
                    'booked_at' => now()->subDays(10 + $index),
                    'confirmed_at' => now()->subDays(10 + $index),
                    'accepted_at' => now()->subDays(10 + $index),
                    'completed_at' => now()->subDays(9 + $index),
                ],
            );

            AppointmentReview::query()->updateOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'patient_user_id' => $reviewPatient->id,
                    'doctor_profile_id' => $doctorProfile->id,
                    'rating' => $row['rating'],
                    'comment' => $row['comment'],
                    'is_visible' => true,
                ],
            );
        }
    }

    private function seedDoctorScheduleAndSlots(Provider $provider, DoctorProfile $doctorProfile, ProviderBranch $branch): void
    {
        $schedule = DoctorSchedule::query()->updateOrCreate(
            ['doctor_profile_id' => $doctorProfile->id, 'name' => 'Pilot demo 10:00-16:00'],
            [
                'provider_id' => $provider->id,
                'branch_id' => $branch->id,
                'is_active' => true,
                'slot_duration_minutes' => 30,
                'buffer_minutes' => 0,
                'max_days_ahead' => 14,
            ],
        );

        for ($day = 0; $day <= 6; $day++) {
            DoctorScheduleDay::query()->updateOrCreate(
                ['doctor_schedule_id' => $schedule->id, 'day_of_week' => $day],
                [
                    'start_time' => '10:00:00',
                    'end_time' => '16:00:00',
                    'is_active' => true,
                ],
            );
        }

        $startDate = CarbonImmutable::tomorrow()->startOfDay();
        for ($date = $startDate; $date->lt($startDate->addDays(14)); $date = $date->addDay()) {
            for ($time = $date->setTime(10, 0); $time->lt($date->setTime(16, 0)); $time = $time->addMinutes(30)) {
                AppointmentSlot::query()->updateOrCreate(
                    [
                        'doctor_profile_id' => $doctorProfile->id,
                        'starts_at' => $time,
                        'ends_at' => $time->addMinutes(30),
                    ],
                    [
                        'provider_id' => $provider->id,
                        'branch_id' => $branch->id,
                        'status' => AppointmentSlotStatus::Available,
                        'hold_expires_at' => null,
                        'generated_from_schedule_id' => $schedule->id,
                    ],
                );
            }
        }
    }

    private function seedPaymentMethods(): void
    {
        PaymentMethod::query()->updateOrCreate(
            ['type' => PaymentMethodType::ManualVodafoneCash->value],
            [
                'name_ar' => 'فودافون كاش',
                'name_en' => 'Vodafone Cash',
                'is_active' => true,
                'config' => ['demo_only' => true, 'number' => '01000000000'],
                'instructions_ar' => 'حوّل المبلغ على رقم الاختبار 01000000000 ثم ارفع صورة إثبات الدفع. هذا رقم تجريبي فقط ولا ترسل أموال حقيقية.',
                'instructions_en' => 'Transfer to the local test number 01000000000, then upload proof. Demo only; do not send real money.',
                'sort_order' => 1,
            ],
        );

        PaymentMethod::query()->updateOrCreate(
            ['type' => PaymentMethodType::ManualInstapay->value],
            [
                'name_ar' => 'إنستاباي',
                'name_en' => 'InstaPay',
                'is_active' => true,
                'config' => ['demo_only' => true, 'handle' => 'etamen.demo@instapay'],
                'instructions_ar' => 'استخدم المعرف etamen.demo@instapay للتجربة فقط، ولا ترسل أموال حقيقية.',
                'instructions_en' => 'Use etamen.demo@instapay for testing only. Do not send real money.',
                'sort_order' => 2,
            ],
        );

        PaymentMethod::query()->where('type', PaymentMethodType::Paymob->value)->update([
            'is_active' => false,
            'config' => null,
            'instructions_ar' => 'Paymob غير مفعل في داتا التجربة المحلية بدون إعدادات sandbox من السيرفر.',
            'instructions_en' => 'Paymob is inactive for local demo data unless backend sandbox config exists.',
        ]);
    }

    private function seedPharmacy(User $pharmacyUser, User $admin, City $city, Area $area): Provider
    {
        $provider = $this->provider(
            type: ProviderType::Pharmacy,
            owner: $pharmacyUser,
            admin: $admin,
            slug: 'pilot-demo-pharmacy',
            nameAr: 'صيدلية اطمن التجريبية',
            nameEn: 'Etamen Demo Pharmacy',
            phone: '01000000003',
            descriptionAr: 'صيدلية تجريبية لاختبار طلبات الدواء والروشتات.',
            descriptionEn: 'Demo pharmacy for local/staging pharmacy order testing.',
        );

        $this->providerStaff($provider, $pharmacyUser, ProviderStaffRole::Owner);

        ProviderBranch::query()->updateOrCreate(
            ['provider_id' => $provider->id, 'name_en' => 'Pilot Nasr City Pharmacy'],
            [
                'city_id' => $city->id,
                'area_id' => $area->id,
                'name_ar' => 'فرع صيدلية اطمن التجريبي',
                'phone' => '01000000003',
                'address_ar' => 'عنوان صيدلية تجريبي في مدينة نصر.',
                'address_en' => 'Demo pharmacy address in Nasr City.',
                'is_main' => true,
                'is_active' => true,
            ],
        );

        PharmacyProfile::query()->updateOrCreate(
            ['provider_id' => $provider->id],
            [
                'license_number' => 'DEMO-PH-001',
                'delivery_available' => true,
            ],
        );

        PharmacyProduct::query()->updateOrCreate(
            ['provider_id' => $provider->id, 'sku' => 'PILOT-PANADOL-DEMO'],
            [
                'name_ar' => 'بانادول تجريبي',
                'name_en' => 'Panadol Demo',
                'description_ar' => 'منتج تجريبي لاختبار طلبات الصيدلية فقط.',
                'description_en' => 'Demo product for pharmacy order testing only.',
                'price' => 45,
                'requires_prescription' => false,
                'stock_quantity' => 50,
                'is_active' => true,
                'metadata' => ['pilot_demo' => true],
            ],
        );

        PharmacyProduct::query()->updateOrCreate(
            ['provider_id' => $provider->id, 'sku' => 'PILOT-RX-DEMO'],
            [
                'name_ar' => 'دواء بروشتة تجريبي',
                'name_en' => 'Prescription Demo Medicine',
                'description_ar' => 'منتج تجريبي يتطلب روشتة لاختبار رفع الروشتة فقط.',
                'description_en' => 'Demo prescription-required product for upload testing only.',
                'price' => 120,
                'requires_prescription' => true,
                'stock_quantity' => 20,
                'is_active' => true,
                'metadata' => ['pilot_demo' => true],
            ],
        );

        foreach ([
            ['sku' => 'PILOT-VITAMIN-D-DEMO', 'name' => 'Vitamin D Demo', 'price' => 95, 'rx' => false, 'stock' => 35],
            ['sku' => 'PILOT-ORS-DEMO', 'name' => 'ORS Sachets Demo', 'price' => 25, 'rx' => false, 'stock' => 80],
            ['sku' => 'PILOT-THERMOMETER-DEMO', 'name' => 'Digital Thermometer Demo', 'price' => 180, 'rx' => false, 'stock' => 18],
            ['sku' => 'PILOT-ANTIBIOTIC-RX-DEMO', 'name' => 'Antibiotic RX Demo', 'price' => 160, 'rx' => true, 'stock' => 15],
        ] as $product) {
            PharmacyProduct::query()->updateOrCreate(
                ['provider_id' => $provider->id, 'sku' => $product['sku']],
                [
                    'name_ar' => $product['name'],
                    'name_en' => $product['name'],
                    'description_ar' => 'Local demo pharmacy product for QA only.',
                    'description_en' => 'Local demo pharmacy product for QA only.',
                    'price' => $product['price'],
                    'requires_prescription' => $product['rx'],
                    'stock_quantity' => $product['stock'],
                    'is_active' => true,
                    'metadata' => ['pilot_demo' => true, 'sprint66' => true],
                ],
            );
        }

        return $provider;
    }

    private function seedLab(User $labUser, User $admin, City $city, Area $area): Provider
    {
        $provider = $this->provider(
            type: ProviderType::Lab,
            owner: $labUser,
            admin: $admin,
            slug: 'pilot-demo-lab',
            nameAr: 'معمل اطمن التجريبي',
            nameEn: 'Etamen Demo Lab',
            phone: '01000000004',
            descriptionAr: 'معمل تجريبي لاختبار طلبات التحاليل والنتائج.',
            descriptionEn: 'Demo lab for local/staging lab order testing.',
        );

        $this->providerStaff($provider, $labUser, ProviderStaffRole::Owner);

        ProviderBranch::query()->updateOrCreate(
            ['provider_id' => $provider->id, 'name_en' => 'Pilot Nasr City Lab'],
            [
                'city_id' => $city->id,
                'area_id' => $area->id,
                'name_ar' => 'فرع معمل اطمن التجريبي',
                'phone' => '01000000004',
                'address_ar' => 'عنوان معمل تجريبي في مدينة نصر.',
                'address_en' => 'Demo lab address in Nasr City.',
                'is_main' => true,
                'is_active' => true,
            ],
        );

        LabProfile::query()->updateOrCreate(
            ['provider_id' => $provider->id],
            [
                'license_number' => 'DEMO-LAB-001',
                'home_collection_available' => true,
            ],
        );

        $cbc = LabTest::query()->updateOrCreate(
            ['provider_id' => $provider->id, 'code' => 'PILOT-CBC-DEMO'],
            [
                'name_ar' => 'صورة دم كاملة تجريبية',
                'name_en' => 'CBC Demo',
                'description_ar' => 'تحليل تجريبي لاختبار طلبات المعمل.',
                'description_en' => 'Demo CBC test for lab order testing.',
                'price' => 180,
                'sample_type' => 'blood',
                'preparation_instructions_ar' => 'لا توجد تعليمات خاصة للتجربة.',
                'preparation_instructions_en' => 'No special demo preparation.',
                'result_time_hours' => 24,
                'is_active' => true,
                'metadata' => ['pilot_demo' => true],
            ],
        );

        $sugar = LabTest::query()->updateOrCreate(
            ['provider_id' => $provider->id, 'code' => 'PILOT-SUGAR-DEMO'],
            [
                'name_ar' => 'سكر الدم تجريبي',
                'name_en' => 'Blood Sugar Demo',
                'description_ar' => 'تحليل سكر تجريبي لاختبار طلبات المعمل.',
                'description_en' => 'Demo blood sugar test for lab order testing.',
                'price' => 90,
                'sample_type' => 'blood',
                'preparation_instructions_ar' => 'اتبع تعليمات المعمل عند الاختبار الحقيقي.',
                'preparation_instructions_en' => 'Follow lab instructions for real tests.',
                'result_time_hours' => 12,
                'is_active' => true,
                'metadata' => ['pilot_demo' => true],
            ],
        );

        foreach ([
            ['code' => 'PILOT-LIVER-DEMO', 'name' => 'Liver Function Demo', 'price' => 210, 'hours' => 24],
            ['code' => 'PILOT-KIDNEY-DEMO', 'name' => 'Kidney Function Demo', 'price' => 190, 'hours' => 24],
            ['code' => 'PILOT-LIPID-DEMO', 'name' => 'Lipid Profile Demo', 'price' => 260, 'hours' => 24],
            ['code' => 'PILOT-THYROID-DEMO', 'name' => 'Thyroid Profile Demo', 'price' => 320, 'hours' => 48],
        ] as $test) {
            LabTest::query()->updateOrCreate(
                ['provider_id' => $provider->id, 'code' => $test['code']],
                [
                    'name_ar' => $test['name'],
                    'name_en' => $test['name'],
                    'description_ar' => 'Local demo lab test for QA only. No medical interpretation is included.',
                    'description_en' => 'Local demo lab test for QA only. No medical interpretation is included.',
                    'price' => $test['price'],
                    'sample_type' => 'blood',
                    'preparation_instructions_ar' => 'Demo preparation instructions only.',
                    'preparation_instructions_en' => 'Demo preparation instructions only.',
                    'result_time_hours' => $test['hours'],
                    'is_active' => true,
                    'metadata' => ['pilot_demo' => true, 'sprint66' => true],
                ],
            );
        }

        $package = LabPackage::query()->updateOrCreate(
            ['provider_id' => $provider->id, 'name_en' => 'Basic Checkup Demo'],
            [
                'name_ar' => 'باقة فحص أساسي تجريبية',
                'description_ar' => 'باقة تجريبية تشمل صورة دم وسكر الدم.',
                'description_en' => 'Demo package including CBC and blood sugar.',
                'price' => 240,
                'is_active' => true,
                'metadata' => ['pilot_demo' => true],
            ],
        );
        $package->tests()->syncWithoutDetaching([$cbc->id, $sugar->id]);

        return $provider;
    }

    private function seedHealthData(User $patient): void
    {
        HealthProfile::query()->updateOrCreate(
            ['patient_user_id' => $patient->id],
            [
                'date_of_birth' => '1990-01-01',
                'gender' => Gender::Male,
                'height_cm' => 175,
                'weight_kg' => 78,
                'blood_type' => BloodType::OPositive,
                'notes' => 'Demo health profile for local/staging QA only.',
            ],
        );

        $records = [
            [VitalType::BloodPressure, now()->subDays(2)->setTime(9, 0), 120, 80, 'mmHg', 'قراءة ضغط تجريبية.'],
            [VitalType::BloodSugar, now()->subDay()->setTime(8, 30), 105, null, 'mg/dL', 'قراءة سكر تجريبية.'],
            [VitalType::Weight, now()->setTime(7, 45), 78, null, 'kg', 'وزن تجريبي.'],
        ];

        foreach ($records as [$type, $measuredAt, $value, $secondary, $unit, $notes]) {
            VitalRecord::query()->updateOrCreate(
                [
                    'patient_user_id' => $patient->id,
                    'vital_type' => $type->value,
                    'measured_at' => $measuredAt,
                ],
                [
                    'value_decimal' => $value,
                    'value_secondary_decimal' => $secondary,
                    'unit' => $unit,
                    'source' => VitalSource::Manual,
                    'flag' => VitalFlag::Normal,
                    'notes' => $notes,
                    'metadata' => ['pilot_demo' => true],
                ],
            );
        }
    }

    private function seedMedicationData(User $patient): void
    {
        $reminder = MedicationReminder::query()->updateOrCreate(
            ['patient_user_id' => $patient->id, 'medication_name' => 'Demo Medication'],
            [
                'dosage' => '1',
                'dosage_unit' => 'tablet',
                'instructions' => 'تذكير تجريبي للتنظيم فقط وليس نصيحة علاجية.',
                'frequency_type' => MedicationFrequencyType::TwiceDaily,
                'interval_hours' => null,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonth()->toDateString(),
                'timezone' => 'Africa/Cairo',
                'status' => MedicationReminderStatus::Active,
                'prescribed_by' => null,
                'notes' => 'Local/staging demo reminder.',
                'refill_enabled' => true,
                'refill_quantity' => 30,
                'refill_threshold' => 5,
                'refill_reminder_date' => now()->addDays(10)->toDateString(),
                'source' => MedicationReminderSource::PatientEntered,
                'metadata' => ['pilot_demo' => true],
            ],
        );

        foreach ([['09:00:00', 'صباحًا'], ['21:00:00', 'مساءً']] as [$time, $label]) {
            MedicationReminderTime::query()->updateOrCreate(
                ['medication_reminder_id' => $reminder->id, 'time_of_day' => $time],
                [
                    'label' => $label,
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedCarePlan(User $patient, User $admin, Provider $doctorProvider): void
    {
        $plan = CarePlan::query()->updateOrCreate(
            ['patient_user_id' => $patient->id, 'title' => 'خطة متابعة غذائية تجريبية'],
            [
                'assigned_by_user_id' => $admin->id,
                'provider_id' => $doctorProvider->id,
                'plan_type' => CarePlanType::Nutrition,
                'description' => 'خطة demo لاختبار أيام الخطة والوجبات والمتابعة.',
                'goal_text' => 'تنظيم الوجبات ومتابعة الالتزام داخل التطبيق.',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(14)->toDateString(),
                'status' => CarePlanStatus::Active,
                'visibility' => CarePlanVisibility::ProviderAssigned,
                'source' => CarePlanSource::ProviderAssigned,
                'notes' => 'Demo only. No diagnosis or treatment claim.',
                'safety_disclaimer' => 'هذه الخطة للتنظيم والمتابعة فقط، ولا تعتبر تشخيصًا أو علاجًا طبيًا. لا تغيّر دواء أو نظام علاجي بدون الرجوع للطبيب أو المختص.',
                'metadata' => ['pilot_demo' => true],
            ],
        );

        $day = CarePlanDay::query()->updateOrCreate(
            ['care_plan_id' => $plan->id, 'day_number' => 1],
            [
                'day_date' => now()->toDateString(),
                'title' => 'اليوم التجريبي الأول',
                'instructions' => 'اتبع الخطة كتجربة واجهة فقط.',
                'is_active' => true,
            ],
        );

        $meals = [
            [CarePlanMealType::Breakfast, 'فطار تجريبي', 'زبادي وشوفان أو بديل مناسب حسب إرشادات المختص.', 1],
            [CarePlanMealType::Lunch, 'غداء تجريبي', 'بروتين وخضار ونشويات بكمية معتدلة.', 2],
            [CarePlanMealType::Dinner, 'عشاء تجريبي', 'وجبة خفيفة منظمة قبل النوم.', 3],
        ];

        foreach ($meals as [$type, $title, $description, $order]) {
            CarePlanMeal::query()->updateOrCreate(
                ['care_plan_day_id' => $day->id, 'meal_type' => $type->value],
                [
                    'title' => $title,
                    'description' => $description,
                    'calories' => null,
                    'protein_g' => null,
                    'carbs_g' => null,
                    'fat_g' => null,
                    'instructions' => 'قيم الوجبات تقريبية للمتابعة فقط.',
                    'sort_order' => $order,
                    'is_required' => true,
                ],
            );
        }

        $foods = [
            [CarePlanFoodCategory::Recommended, 'خضروات طازجة', 'مقترح ضمن الخطة التجريبية.'],
            [CarePlanFoodCategory::Limited, 'حلويات', 'بكميات محدودة حسب الخطة.'],
            [CarePlanFoodCategory::Allowed, 'مياه', 'مسموح ومهم للمتابعة العامة.'],
        ];

        foreach ($foods as [$category, $name, $notes]) {
            CarePlanFoodItem::query()->updateOrCreate(
                ['care_plan_id' => $plan->id, 'category' => $category->value, 'name' => $name],
                ['notes' => $notes],
            );
        }

        CarePlanInstruction::query()->updateOrCreate(
            ['care_plan_id' => $plan->id, 'instruction_type' => CarePlanInstructionType::General->value, 'title' => 'تنبيه تجربة'],
            [
                'body' => 'الخطة تجريبية للمتابعة داخل التطبيق ولا تعتبر نصيحة طبية.',
                'sort_order' => 1,
            ],
        );
    }

    private function seedNotification(User $patient): void
    {
        Notification::query()->updateOrCreate(
            ['user_id' => $patient->id, 'type' => 'pilot_demo_welcome'],
            [
                'category' => NotificationCategory::System,
                'title' => 'مرحبًا بك في اطمن',
                'body' => 'هذا إشعار تجريبي لاختبار التنبيهات داخل التطبيق.',
                'data' => ['source' => 'pilot_demo', 'safe_entity' => 'system'],
                'priority' => NotificationPriority::Normal,
                'read_at' => null,
                'action_url' => null,
            ],
        );
    }

    private function seedDemoLabResultOrder(User $patient, User $labUser, Provider $labProvider): void
    {
        $test = LabTest::query()
            ->where('provider_id', $labProvider->id)
            ->where('code', 'PILOT-CBC-DEMO')
            ->firstOrFail();

        $order = LabOrder::query()->updateOrCreate(
            ['order_number' => 'LAB-PILOT-RESULT-001'],
            [
                'patient_user_id' => $patient->id,
                'lab_provider_id' => $labProvider->id,
                'payment_id' => null,
                'subtotal' => 180,
                'discount_total' => 0,
                'commission_amount' => 0,
                'provider_net_amount' => 180,
                'grand_total' => 180,
                'currency' => 'EGP',
                'payment_status' => LabOrderPaymentStatus::Paid,
                'order_status' => LabOrderStatus::ResultReady,
                'sample_collection_method' => LabSampleCollectionMethod::BranchVisit,
                'collection_address' => null,
                'scheduled_at' => now()->subDays(2),
                'paid_at' => now()->subDays(2),
                'accepted_at' => now()->subDays(2),
                'sample_collected_at' => now()->subDay(),
                'result_ready_at' => now(),
                'notes' => 'Demo result order for local/staging result download testing.',
                'metadata' => ['pilot_demo' => true],
            ],
        );

        LabOrderItem::query()->updateOrCreate(
            ['order_id' => $order->id, 'item_type' => LabOrderItemType::Test->value, 'test_id' => $test->id],
            [
                'package_id' => null,
                'item_name' => $test->name_en,
                'unit_price' => 180,
                'quantity' => 1,
                'line_total' => 180,
            ],
        );

        $path = 'pilot-demo/lab-result-demo.txt';
        $contents = "Etamen pilot demo lab result.\nThis is not real medical data.\n";
        Storage::disk('medical_private')->put($path, $contents);

        $file = UploadedFile::query()->updateOrCreate(
            ['disk' => 'medical_private', 'path' => $path],
            [
                'owner_type' => LabOrder::class,
                'owner_id' => $order->id,
                'uploaded_by' => $labUser->id,
                'original_name' => 'pilot-demo-lab-result.txt',
                'mime_type' => 'text/plain',
                'size' => strlen($contents),
                'file_category' => FileCategory::LabResult,
                'visibility' => FileVisibility::Private,
                'checksum' => hash('sha256', $contents),
                'metadata' => ['pilot_demo' => true],
            ],
        );

        $result = LabResult::query()->updateOrCreate(
            ['order_id' => $order->id, 'file_id' => $file->id],
            [
                'uploaded_by' => $labUser->id,
                'title_ar' => 'نتيجة تحليل تجريبية',
                'title_en' => 'Demo Lab Result',
                'notes' => 'Demo file for authorized download testing only.',
                'status' => LabResultStatus::VisibleToPatient,
            ],
        );

        $file->update([
            'owner_type' => LabResult::class,
            'owner_id' => $result->id,
        ]);
    }

    private function seedPharmacyLabHistoryStates(User $patient, Provider $pharmacyProvider, Provider $labProvider, string $orderPrefix = ''): void
    {
        $product = PharmacyProduct::query()
            ->where('provider_id', $pharmacyProvider->id)
            ->orderBy('id')
            ->first();

        if ($product) {
            $pharmacyRows = [
                ['PHARM-HISTORY-REVIEW', PharmacyOrderStatus::PharmacyReview, PharmacyOrderPaymentStatus::Unpaid, 'في انتظار مراجعة الصيدلية', 0],
                ['PHARM-HISTORY-AWAITING-PAY', PharmacyOrderStatus::AwaitingPayment, PharmacyOrderPaymentStatus::Unpaid, 'في انتظار الدفع', 1],
                ['PHARM-HISTORY-PREPARING', PharmacyOrderStatus::Preparing, PharmacyOrderPaymentStatus::Paid, 'طلب صيدلية تحت التجهيز', 2],
                ['PHARM-HISTORY-READY', PharmacyOrderStatus::ReadyForPickup, PharmacyOrderPaymentStatus::Paid, 'طلب صيدلية جاهز', 3],
                ['PHARM-HISTORY-DELIVERY', PharmacyOrderStatus::OutForDelivery, PharmacyOrderPaymentStatus::Paid, 'طلب صيدلية في التوصيل', 4],
                ['PHARM-HISTORY-COMPLETE', PharmacyOrderStatus::Delivered, PharmacyOrderPaymentStatus::Paid, 'طلب صيدلية مكتمل', 5],
                ['PHARM-HISTORY-REJECTED', PharmacyOrderStatus::Rejected, PharmacyOrderPaymentStatus::Rejected, 'طلب صيدلية مرفوض', 6],
                ['PHARM-HISTORY-CANCELLED', PharmacyOrderStatus::Cancelled, PharmacyOrderPaymentStatus::Unpaid, 'طلب صيدلية ملغي', 7],
            ];

            foreach ($pharmacyRows as [$number, $status, $paymentStatus, $notes, $age]) {
                $number = $orderPrefix.$number;
                $price = (float) $product->price;
                $order = PharmacyOrder::query()->updateOrCreate(
                    ['order_number' => $number],
                    [
                        'patient_user_id' => $patient->id,
                        'pharmacy_provider_id' => $pharmacyProvider->id,
                        'prescription_id' => null,
                        'payment_id' => null,
                        'subtotal' => $price,
                        'discount_total' => 0,
                        'commission_amount' => 0,
                        'provider_net_amount' => $price,
                        'grand_total' => $price,
                        'currency' => 'EGP',
                        'payment_status' => $paymentStatus,
                        'order_status' => $status,
                        'delivery_method' => PharmacyDeliveryMethod::Delivery,
                        'delivery_address' => 'Local demo address only',
                        'notes' => $notes,
                        'paid_at' => $paymentStatus === PharmacyOrderPaymentStatus::Paid ? now()->subDays($age) : null,
                        'accepted_at' => in_array($status, [
                            PharmacyOrderStatus::Accepted,
                            PharmacyOrderStatus::AwaitingPayment,
                            PharmacyOrderStatus::Paid,
                            PharmacyOrderStatus::Preparing,
                            PharmacyOrderStatus::ReadyForPickup,
                            PharmacyOrderStatus::OutForDelivery,
                            PharmacyOrderStatus::Delivered,
                        ], true) ? now()->subDays($age + 1) : null,
                        'rejected_at' => $status === PharmacyOrderStatus::Rejected ? now()->subDays($age) : null,
                        'delivered_at' => $status === PharmacyOrderStatus::Delivered ? now()->subDays($age) : null,
                        'cancelled_at' => $status === PharmacyOrderStatus::Cancelled ? now()->subDays($age) : null,
                        'metadata' => ['pilot_demo' => true, 'history_state' => $status->value],
                        'created_at' => now()->subDays($age + 1),
                    ],
                );

                PharmacyOrderItem::query()->updateOrCreate(
                    ['order_id' => $order->id, 'product_id' => $product->id],
                    [
                        'product_name' => $product->name_en,
                        'unit_price' => $price,
                        'quantity' => 1,
                        'line_total' => $price,
                    ],
                );
            }
        }

        $test = LabTest::query()
            ->where('provider_id', $labProvider->id)
            ->orderBy('id')
            ->first();

        if (! $test) {
            return;
        }

        $labRows = [
            ['LAB-HISTORY-REVIEW', LabOrderStatus::LabReview, LabOrderPaymentStatus::Unpaid, 'طلب معمل تحت المراجعة', 0],
            ['LAB-HISTORY-AWAITING-PAY', LabOrderStatus::AwaitingPayment, LabOrderPaymentStatus::Unpaid, 'طلب معمل في انتظار الدفع', 1],
            ['LAB-HISTORY-ACCEPTED', LabOrderStatus::Accepted, LabOrderPaymentStatus::Unpaid, 'طلب معمل مقبول', 2],
            ['LAB-HISTORY-SAMPLE', LabOrderStatus::SampleCollected, LabOrderPaymentStatus::Paid, 'تم جمع العينة', 3],
            ['LAB-HISTORY-PROCESSING', LabOrderStatus::Processing, LabOrderPaymentStatus::Paid, 'جاري التحليل', 4],
            ['LAB-HISTORY-RESULT', LabOrderStatus::ResultReady, LabOrderPaymentStatus::Paid, 'النتيجة جاهزة بدون تفسير طبي', 5],
            ['LAB-HISTORY-COMPLETE', LabOrderStatus::Completed, LabOrderPaymentStatus::Paid, 'طلب معمل مكتمل', 6],
            ['LAB-HISTORY-REJECTED', LabOrderStatus::Rejected, LabOrderPaymentStatus::Unpaid, 'طلب معمل مرفوض', 7],
            ['LAB-HISTORY-CANCELLED', LabOrderStatus::Cancelled, LabOrderPaymentStatus::Unpaid, 'طلب معمل ملغي', 8],
        ];

        foreach ($labRows as [$number, $status, $paymentStatus, $notes, $age]) {
            $number = $orderPrefix.$number;
            $price = (float) $test->price;
            $order = LabOrder::query()->updateOrCreate(
                ['order_number' => $number],
                [
                    'patient_user_id' => $patient->id,
                    'lab_provider_id' => $labProvider->id,
                    'payment_id' => null,
                    'subtotal' => $price,
                    'discount_total' => 0,
                    'commission_amount' => 0,
                    'provider_net_amount' => $price,
                    'grand_total' => $price,
                    'currency' => 'EGP',
                    'payment_status' => $paymentStatus,
                    'order_status' => $status,
                    'sample_collection_method' => $age % 2 === 0 ? LabSampleCollectionMethod::BranchVisit : LabSampleCollectionMethod::HomeCollection,
                    'collection_address' => $age % 2 === 0 ? null : 'Local demo home collection address',
                    'scheduled_at' => now()->addDays(max(1, 9 - $age)),
                    'paid_at' => $paymentStatus === LabOrderPaymentStatus::Paid ? now()->subDays($age) : null,
                    'accepted_at' => in_array($status, [
                        LabOrderStatus::Accepted,
                        LabOrderStatus::Paid,
                        LabOrderStatus::SampleScheduled,
                        LabOrderStatus::SampleCollected,
                        LabOrderStatus::Processing,
                        LabOrderStatus::ResultReady,
                        LabOrderStatus::Completed,
                    ], true) ? now()->subDays($age + 1) : null,
                    'rejected_at' => $status === LabOrderStatus::Rejected ? now()->subDays($age) : null,
                    'sample_collected_at' => in_array($status, [
                        LabOrderStatus::SampleCollected,
                        LabOrderStatus::Processing,
                        LabOrderStatus::ResultReady,
                        LabOrderStatus::Completed,
                    ], true) ? now()->subDays($age) : null,
                    'result_ready_at' => in_array($status, [LabOrderStatus::ResultReady, LabOrderStatus::Completed], true) ? now()->subDays($age) : null,
                    'completed_at' => $status === LabOrderStatus::Completed ? now()->subDays($age) : null,
                    'cancelled_at' => $status === LabOrderStatus::Cancelled ? now()->subDays($age) : null,
                    'notes' => $notes,
                    'metadata' => ['pilot_demo' => true, 'history_state' => $status->value],
                    'created_at' => now()->subDays($age + 1),
                ],
            );

            LabOrderItem::query()->updateOrCreate(
                ['order_id' => $order->id, 'item_type' => LabOrderItemType::Test->value, 'test_id' => $test->id],
                [
                    'package_id' => null,
                    'item_name' => $test->name_en,
                    'unit_price' => $price,
                    'quantity' => 1,
                    'line_total' => $price,
                ],
            );
        }
    }

    private function seedExpandedDemoCatalog(User $admin, City $city, Area $area): void
    {
        $patients = [
            ['demo.patient.asmaa@example.test', 'Demo Patient Asmaa', '01000001001', Gender::Female],
            ['demo.patient.omar@example.test', 'Demo Patient Omar', '01000001002', Gender::Male],
            ['demo.patient.mona@example.test', 'Demo Patient Mona', '01000001003', Gender::Female],
        ];

        foreach ($patients as [$email, $name, $phone, $gender]) {
            $patient = $this->demoUser($email, $name, UserRole::Patient);
            $patient->patientProfile()->updateOrCreate(
                ['user_id' => $patient->id],
                [
                    'phone' => $phone,
                    'date_of_birth' => '1992-06-15',
                    'gender' => $gender->value,
                    'metadata' => ['pilot_demo' => true, 'expanded_demo' => true],
                ],
            );

            $this->seedHealthData($patient);
            $this->seedMedicationData($patient);
            $this->seedNotification($patient);
        }

        $doctors = [
            [
                'email' => 'demo.doctor.derma@example.test',
                'name' => 'Demo Dermatology Doctor',
                'slug' => 'demo-dermatology-doctor',
                'name_ar' => 'د. سارة التجريبية',
                'name_en' => 'Dr Sara Skin Demo',
                'specialty_slug' => 'dermatology-demo',
                'specialty_ar' => 'جلدية تجريبية',
                'specialty_en' => 'Dermatology',
                'phone' => '01000002001',
                'fee' => 250,
                'experience' => 6,
                'avatar_path' => 'legacy-doctorfinder/demo-doctor-avatar-2.png',
            ],
            [
                'email' => 'demo.doctor.pedia@example.test',
                'name' => 'Demo Pediatrics Doctor',
                'slug' => 'demo-pediatrics-doctor',
                'name_ar' => 'د. يوسف التجريبي',
                'name_en' => 'Dr Youssef Kids Demo',
                'specialty_slug' => 'pediatrics-demo',
                'specialty_ar' => 'أطفال تجريبي',
                'specialty_en' => 'Pediatrics',
                'phone' => '01000002002',
                'fee' => 220,
                'experience' => 10,
                'avatar_path' => 'legacy-doctorfinder/demo-doctor-avatar-3.png',
            ],
            [
                'email' => 'demo.doctor.ortho@example.test',
                'name' => 'Demo Orthopedics Doctor',
                'slug' => 'demo-orthopedics-doctor',
                'name_ar' => 'د. كريم التجريبي',
                'name_en' => 'Dr Karim Bones Demo',
                'specialty_slug' => 'orthopedics-demo',
                'specialty_ar' => 'عظام تجريبي',
                'specialty_en' => 'Orthopedics',
                'phone' => '01000002003',
                'fee' => 350,
                'experience' => 12,
                'avatar_path' => 'legacy-doctorfinder/demo-doctor-avatar-1.png',
            ],
        ];

        foreach ($doctors as $doctor) {
            $doctorUser = $this->demoUser($doctor['email'], $doctor['name'], UserRole::Doctor);
            $this->seedAdditionalDoctor($doctorUser, $admin, $city, $area, $doctor);
        }

        $pharmacies = [
            [
                'email' => 'demo.pharmacy.nasr@example.test',
                'name' => 'Demo Pharmacy Nasr City Admin',
                'slug' => 'demo-pharmacy-nasr-city',
                'name_ar' => 'Demo Pharmacy Nasr City',
                'name_en' => 'Demo Pharmacy Nasr City',
                'phone' => '01000003001',
                'license' => 'DEMO-PH-002',
                'products' => [
                    ['sku' => 'DEMO-VITAMIN-C', 'name' => 'Vitamin C Demo', 'price' => 75, 'stock' => 80, 'rx' => false],
                    ['sku' => 'DEMO-ANTIBIOTIC-RX', 'name' => 'Antibiotic RX Demo', 'price' => 160, 'stock' => 25, 'rx' => true],
                    ['sku' => 'DEMO-BANDAGE', 'name' => 'Bandage Demo', 'price' => 35, 'stock' => 120, 'rx' => false],
                ],
            ],
            [
                'email' => 'demo.pharmacy.maadi@example.test',
                'name' => 'Demo Pharmacy Maadi Admin',
                'slug' => 'demo-pharmacy-maadi',
                'name_ar' => 'Demo Pharmacy Maadi',
                'name_en' => 'Demo Pharmacy Maadi',
                'phone' => '01000003002',
                'license' => 'DEMO-PH-003',
                'products' => [
                    ['sku' => 'DEMO-PAINKILLER', 'name' => 'Painkiller Demo', 'price' => 55, 'stock' => 60, 'rx' => false],
                    ['sku' => 'DEMO-INHALER-RX', 'name' => 'Inhaler RX Demo', 'price' => 210, 'stock' => 15, 'rx' => true],
                ],
            ],
        ];

        foreach ($pharmacies as $pharmacy) {
            $pharmacyUser = $this->demoUser($pharmacy['email'], $pharmacy['name'], UserRole::PharmacyAdmin);
            $this->seedAdditionalPharmacy($pharmacyUser, $admin, $city, $area, $pharmacy);
        }

        $labs = [
            [
                'email' => 'demo.lab.nasr@example.test',
                'name' => 'Demo Lab Nasr Admin',
                'slug' => 'demo-lab-nasr-city',
                'name_ar' => 'Demo Lab Nasr City',
                'name_en' => 'Demo Lab Nasr City',
                'phone' => '01000004001',
                'license' => 'DEMO-LAB-002',
                'tests' => [
                    ['code' => 'DEMO-LIVER', 'name' => 'Liver Functions Demo', 'price' => 260, 'hours' => 24],
                    ['code' => 'DEMO-KIDNEY', 'name' => 'Kidney Functions Demo', 'price' => 230, 'hours' => 24],
                    ['code' => 'DEMO-LIPID', 'name' => 'Lipid Profile Demo', 'price' => 300, 'hours' => 48],
                ],
            ],
            [
                'email' => 'demo.lab.maadi@example.test',
                'name' => 'Demo Lab Maadi Admin',
                'slug' => 'demo-lab-maadi',
                'name_ar' => 'Demo Lab Maadi',
                'name_en' => 'Demo Lab Maadi',
                'phone' => '01000004002',
                'license' => 'DEMO-LAB-003',
                'tests' => [
                    ['code' => 'DEMO-TSH', 'name' => 'TSH Demo', 'price' => 190, 'hours' => 24],
                    ['code' => 'DEMO-VITD', 'name' => 'Vitamin D Demo', 'price' => 450, 'hours' => 48],
                ],
            ],
        ];

        foreach ($labs as $lab) {
            $labUser = $this->demoUser($lab['email'], $lab['name'], UserRole::LabAdmin);
            $this->seedAdditionalLab($labUser, $admin, $city, $area, $lab);
        }
    }

    private function seedHospital(User $hospitalUser, User $admin, City $city, Area $area): Provider
    {
        $hospital = $this->provider(
            type: ProviderType::Hospital,
            owner: $hospitalUser,
            admin: $admin,
            slug: 'pilot-demo-hospital',
            nameAr: 'مستشفى اطمن التخصصي',
            nameEn: 'Etamen Specialty Hospital',
            phone: '01000006001',
            descriptionAr: 'مستشفى تجريبي آمن لاختبار قسم المستشفيات محليًا وربط الأطباء بالحجز الحالي.',
            descriptionEn: 'Safe demo hospital for local hospital discovery and booking QA.',
        );
        $this->providerStaff($hospital, $hospitalUser, ProviderStaffRole::Owner);

        HospitalProfile::query()->updateOrCreate(
            ['provider_id' => $hospital->id],
            [
                'license_number' => 'HOSP-DEMO-001',
                'description_ar' => 'مستشفى تجريبي يضم أقسامًا وأطباء للحجز داخل بيئة local/staging فقط.',
                'description_en' => 'Demo specialty hospital with departments and linked doctors for local QA only.',
                'emergency_available' => true,
                'has_outpatient' => true,
                'has_inpatient' => true,
                'has_icu' => true,
                'has_ambulance' => true,
                'is_active' => true,
            ],
        );

        ProviderBookingSetting::query()->updateOrCreate(
            ['provider_id' => $hospital->id],
            [
                'clinic_visit_enabled' => true,
                'online_video_enabled' => false,
                'home_visit_enabled' => false,
                'branch_visit_enabled' => true,
                'booking_requires_payment' => true,
                'pay_at_branch_enabled' => false,
                'default_slot_duration_minutes' => 30,
                'cancellation_policy_ar' => 'سياسة تجريبية محلية فقط.',
                'cancellation_policy_en' => 'Local demo policy only.',
                'is_active' => true,
            ],
        );

        ProviderBranch::query()->updateOrCreate(
            ['provider_id' => $hospital->id, 'name_en' => 'Etamen Specialty Hospital - Nasr City'],
            [
                'city_id' => $city->id,
                'area_id' => $area->id,
                'name_ar' => 'مستشفى اطمن التخصصي - مدينة نصر',
                'phone' => '01000006001',
                'whatsapp' => '01000006001',
                'address_line_1' => 'Demo Hospital Street',
                'district' => 'Nasr City',
                'address_ar' => 'شارع تجريبي، مدينة نصر، القاهرة',
                'address_en' => 'Demo Street, Nasr City, Cairo',
                'latitude' => 30.0561000,
                'longitude' => 31.3300000,
                'is_24_hours' => true,
                'is_main' => true,
                'is_active' => true,
            ],
        );

        $departments = [
            [
                'slug' => 'cardiology-demo',
                'name_ar' => 'قلب وأوعية دموية',
                'name_en' => 'Cardiology',
                'doctor' => [
                    'slug' => 'pilot-demo-doctor',
                    'email' => 'pilot.doctor@example.test',
                    'name' => 'Pilot Doctor',
                    'name_ar' => 'د. أحمد التجريبي',
                    'name_en' => 'Dr Ahmed Demo',
                    'phone' => '01000000002',
                    'fee' => 300,
                    'hospital_fee' => 360,
                    'experience' => 8,
                    'avatar_path' => 'legacy-doctorfinder/demo-doctor-avatar-1.png',
                ],
            ],
            [
                'slug' => 'orthopedics-demo',
                'name_ar' => 'عظام',
                'name_en' => 'Orthopedics',
                'doctor' => [
                    'slug' => 'demo-orthopedics-doctor',
                    'email' => 'demo.doctor.ortho@example.test',
                    'name' => 'Demo Orthopedics Doctor',
                    'name_ar' => 'د. كريم التجريبي',
                    'name_en' => 'Dr Karim Bones Demo',
                    'phone' => '01000002003',
                    'fee' => 350,
                    'hospital_fee' => null,
                    'experience' => 12,
                    'avatar_path' => 'legacy-doctorfinder/demo-doctor-avatar-1.png',
                ],
            ],
            [
                'slug' => 'pediatrics-demo',
                'name_ar' => 'أطفال',
                'name_en' => 'Pediatrics',
                'doctor' => [
                    'slug' => 'demo-pediatrics-doctor',
                    'email' => 'demo.doctor.pedia@example.test',
                    'name' => 'Demo Pediatrics Doctor',
                    'name_ar' => 'د. يوسف التجريبي',
                    'name_en' => 'Dr Youssef Kids Demo',
                    'phone' => '01000002002',
                    'fee' => 220,
                    'hospital_fee' => 240,
                    'experience' => 10,
                    'avatar_path' => 'legacy-doctorfinder/demo-doctor-avatar-3.png',
                ],
            ],
            [
                'slug' => 'dermatology-demo',
                'name_ar' => 'جلدية',
                'name_en' => 'Dermatology',
                'doctor' => [
                    'slug' => 'demo-dermatology-doctor',
                    'email' => 'demo.doctor.derma@example.test',
                    'name' => 'Demo Dermatology Doctor',
                    'name_ar' => 'د. سارة التجريبية',
                    'name_en' => 'Dr Sara Skin Demo',
                    'phone' => '01000002001',
                    'fee' => 250,
                    'hospital_fee' => 275,
                    'experience' => 6,
                    'avatar_path' => 'legacy-doctorfinder/demo-doctor-avatar-2.png',
                ],
            ],
            [
                'slug' => 'obgyn-demo',
                'name_ar' => 'نساء وتوليد',
                'name_en' => 'Obstetrics and Gynecology',
                'doctor' => [
                    'slug' => 'demo-obgyn-doctor',
                    'email' => 'demo.doctor.obgyn@example.test',
                    'name' => 'Demo OBGYN Doctor',
                    'name_ar' => 'د. مريم التجريبية',
                    'name_en' => 'Dr Mariam OBGYN Demo',
                    'phone' => '01000002004',
                    'fee' => 320,
                    'hospital_fee' => 340,
                    'experience' => 9,
                    'avatar_path' => 'legacy-doctorfinder/demo-doctor-avatar-2.png',
                ],
            ],
        ];

        foreach ($departments as $row) {
            $specialty = Specialty::query()->updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'name_ar' => $row['name_ar'],
                    'name_en' => $row['name_en'],
                    'is_active' => true,
                ],
            );

            $department = HospitalDepartment::query()->updateOrCreate(
                ['hospital_provider_id' => $hospital->id, 'name_en' => $row['name_en']],
                [
                    'specialty_id' => $specialty->id,
                    'name_ar' => $row['name_ar'],
                    'description_ar' => 'قسم تجريبي للحجز المحلي داخل مستشفى اطمن.',
                    'description_en' => 'Demo department for local hospital booking QA.',
                    'is_active' => true,
                ],
            );

            $doctorProvider = $this->ensureHospitalDoctor($row['doctor'], $row, $admin, $city, $area);
            HospitalDoctor::query()->updateOrCreate(
                [
                    'hospital_provider_id' => $hospital->id,
                    'doctor_provider_id' => $doctorProvider->id,
                    'hospital_department_id' => $department->id,
                ],
                [
                    'consultation_fee' => array_key_exists('hospital_fee', $row['doctor'])
                        ? $row['doctor']['hospital_fee']
                        : $doctorProvider->doctorProfile?->consultation_fee,
                    'online_consultation_enabled' => false,
                    'clinic_consultation_enabled' => true,
                    'is_active' => true,
                ],
            );
        }

        return $hospital;
    }

    private function ensureHospitalDoctor(array $doctor, array $department, User $admin, City $city, Area $area): Provider
    {
        $provider = Provider::query()->where('slug', $doctor['slug'])->first();

        if (! $provider) {
            $doctorUser = $this->demoUser($doctor['email'], $doctor['name'], UserRole::Doctor);
            $this->seedAdditionalDoctor($doctorUser, $admin, $city, $area, [
                'slug' => $doctor['slug'],
                'name_ar' => $doctor['name_ar'],
                'name_en' => $doctor['name_en'],
                'specialty_slug' => $department['slug'],
                'specialty_ar' => $department['name_ar'],
                'specialty_en' => $department['name_en'],
                'phone' => $doctor['phone'],
                'fee' => $doctor['fee'],
                'experience' => $doctor['experience'],
                'avatar_path' => $doctor['avatar_path'],
            ]);
            $provider = Provider::query()->where('slug', $doctor['slug'])->firstOrFail();
        }

        return $provider->load('doctorProfile');
    }

    private function seedAdditionalDoctor(User $doctorUser, User $admin, City $city, Area $area, array $doctor): void
    {
        $specialty = Specialty::query()->updateOrCreate(
            ['slug' => $doctor['specialty_slug']],
            [
                'name_ar' => $doctor['specialty_ar'],
                'name_en' => $doctor['specialty_en'],
                'is_active' => true,
            ],
        );

        $provider = $this->provider(
            type: ProviderType::Doctor,
            owner: $doctorUser,
            admin: $admin,
            slug: $doctor['slug'],
            nameAr: $doctor['name_ar'],
            nameEn: $doctor['name_en'],
            phone: $doctor['phone'],
            descriptionAr: 'Expanded local demo doctor for QA.',
            descriptionEn: 'Expanded local demo doctor for QA.',
        );
        $this->providerStaff($provider, $doctorUser, ProviderStaffRole::Owner);

        $doctorProfile = DoctorProfile::query()->updateOrCreate(
            ['provider_id' => $provider->id],
            [
                'user_id' => $doctorUser->id,
                'title' => 'Consultant',
                'bio_ar' => 'Expanded demo doctor profile for local/staging QA only.',
                'bio_en' => 'Expanded demo doctor profile for local/staging QA only.',
                'avatar_path' => $doctor['avatar_path'] ?? 'legacy-doctorfinder/demo-doctor-avatar-1.png',
                'consultation_fee' => $doctor['fee'],
                'years_of_experience' => $doctor['experience'],
            ],
        );
        $doctorProfile->specialties()->syncWithoutDetaching([$specialty->id]);

        $branch = ProviderBranch::query()->updateOrCreate(
            ['provider_id' => $provider->id, 'name_en' => $doctor['name_en'].' Clinic'],
            [
                'city_id' => $city->id,
                'area_id' => $area->id,
                'name_ar' => $doctor['name_en'].' Clinic',
                'phone' => $doctor['phone'],
                'address_ar' => 'Expanded demo clinic address.',
                'address_en' => 'Expanded demo clinic address.',
                'is_main' => true,
                'is_active' => true,
            ],
        );

        $this->seedDoctorScheduleAndSlots($provider, $doctorProfile, $branch);
        $seedPatient = User::query()->where('email', 'pilot.patient@example.test')->first();
        if ($seedPatient) {
            $this->seedDoctorReviews($seedPatient, $provider, $doctorProfile, $branch);
        }
    }

    private function seedAdditionalPharmacy(User $pharmacyUser, User $admin, City $city, Area $area, array $pharmacy): void
    {
        $provider = $this->provider(
            type: ProviderType::Pharmacy,
            owner: $pharmacyUser,
            admin: $admin,
            slug: $pharmacy['slug'],
            nameAr: $pharmacy['name_ar'],
            nameEn: $pharmacy['name_en'],
            phone: $pharmacy['phone'],
            descriptionAr: 'Expanded local demo pharmacy for QA.',
            descriptionEn: 'Expanded local demo pharmacy for QA.',
        );
        $this->providerStaff($provider, $pharmacyUser, ProviderStaffRole::Owner);

        ProviderBranch::query()->updateOrCreate(
            ['provider_id' => $provider->id, 'name_en' => $pharmacy['name_en'].' Branch'],
            [
                'city_id' => $city->id,
                'area_id' => $area->id,
                'name_ar' => $pharmacy['name_en'].' Branch',
                'phone' => $pharmacy['phone'],
                'address_ar' => 'Expanded demo pharmacy address.',
                'address_en' => 'Expanded demo pharmacy address.',
                'is_main' => true,
                'is_active' => true,
            ],
        );

        PharmacyProfile::query()->updateOrCreate(
            ['provider_id' => $provider->id],
            [
                'license_number' => $pharmacy['license'],
                'delivery_available' => true,
            ],
        );

        foreach ($pharmacy['products'] as $product) {
            PharmacyProduct::query()->updateOrCreate(
                ['provider_id' => $provider->id, 'sku' => $product['sku']],
                [
                    'name_ar' => $product['name'],
                    'name_en' => $product['name'],
                    'description_ar' => 'Expanded demo pharmacy product for QA only.',
                    'description_en' => 'Expanded demo pharmacy product for QA only.',
                    'price' => $product['price'],
                    'requires_prescription' => $product['rx'],
                    'stock_quantity' => $product['stock'],
                    'is_active' => true,
                    'metadata' => ['pilot_demo' => true, 'expanded_demo' => true],
                ],
            );
        }
    }

    private function seedAdditionalLab(User $labUser, User $admin, City $city, Area $area, array $lab): void
    {
        $provider = $this->provider(
            type: ProviderType::Lab,
            owner: $labUser,
            admin: $admin,
            slug: $lab['slug'],
            nameAr: $lab['name_ar'],
            nameEn: $lab['name_en'],
            phone: $lab['phone'],
            descriptionAr: 'Expanded local demo lab for QA.',
            descriptionEn: 'Expanded local demo lab for QA.',
        );
        $this->providerStaff($provider, $labUser, ProviderStaffRole::Owner);

        ProviderBranch::query()->updateOrCreate(
            ['provider_id' => $provider->id, 'name_en' => $lab['name_en'].' Branch'],
            [
                'city_id' => $city->id,
                'area_id' => $area->id,
                'name_ar' => $lab['name_en'].' Branch',
                'phone' => $lab['phone'],
                'address_ar' => 'Expanded demo lab address.',
                'address_en' => 'Expanded demo lab address.',
                'is_main' => true,
                'is_active' => true,
            ],
        );

        LabProfile::query()->updateOrCreate(
            ['provider_id' => $provider->id],
            [
                'license_number' => $lab['license'],
                'home_collection_available' => true,
            ],
        );

        $testIds = [];
        foreach ($lab['tests'] as $test) {
            $model = LabTest::query()->updateOrCreate(
                ['provider_id' => $provider->id, 'code' => $test['code']],
                [
                    'name_ar' => $test['name'],
                    'name_en' => $test['name'],
                    'description_ar' => 'Expanded demo lab test for QA only.',
                    'description_en' => 'Expanded demo lab test for QA only.',
                    'price' => $test['price'],
                    'sample_type' => 'blood',
                    'preparation_instructions_ar' => 'Demo preparation instructions.',
                    'preparation_instructions_en' => 'Demo preparation instructions.',
                    'result_time_hours' => $test['hours'],
                    'is_active' => true,
                    'metadata' => ['pilot_demo' => true, 'expanded_demo' => true],
                ],
            );
            $testIds[] = $model->id;
        }

        $package = LabPackage::query()->updateOrCreate(
            ['provider_id' => $provider->id, 'name_en' => $lab['name_en'].' Checkup Package'],
            [
                'name_ar' => $lab['name_en'].' Checkup Package',
                'description_ar' => 'Expanded demo lab package for QA only.',
                'description_en' => 'Expanded demo lab package for QA only.',
                'price' => collect($lab['tests'])->sum('price') * 0.85,
                'is_active' => true,
                'metadata' => ['pilot_demo' => true, 'expanded_demo' => true],
            ],
        );
        $package->tests()->syncWithoutDetaching($testIds);
    }

    private function seedRadiology(User $radiologyUser, User $admin, City $city, Area $area): Provider
    {
        $provider = $this->provider(
            type: ProviderType::Radiology,
            owner: $radiologyUser,
            admin: $admin,
            slug: 'pilot-demo-radiology',
            nameAr: 'مركز أشعة التجريبي',
            nameEn: 'Pilot Demo Radiology Center',
            phone: '01000005001',
            descriptionAr: 'مركز أشعة تجريبي آمن لبيئة local/staging فقط.',
            descriptionEn: 'Safe demo radiology center for local/staging catalog QA only.',
        );
        $this->providerStaff($provider, $radiologyUser, ProviderStaffRole::Owner);

        RadiologyProfile::query()->updateOrCreate(
            ['provider_id' => $provider->id],
            [
                'license_number' => 'RAD-DEMO-001',
                'home_service_enabled' => false,
                'report_delivery_enabled' => true,
                'dicom_supported' => false,
                'description_ar' => 'ملف تجريبي لفهرس الأشعة فقط.',
                'description_en' => 'Demo profile for radiology catalog only.',
                'is_active' => true,
            ],
        );

        $branch = ProviderBranch::query()->updateOrCreate(
            ['provider_id' => $provider->id, 'name_en' => 'Pilot Radiology Nasr City Branch'],
            [
                'city_id' => $city->id,
                'area_id' => $area->id,
                'name_ar' => 'فرع أشعة مدينة نصر التجريبي',
                'phone' => '01000005001',
                'address_line_1' => 'Demo Radiology Tower',
                'district' => 'Nasr City',
                'address_ar' => 'عنوان تجريبي في مدينة نصر لاختبار فهرس الأشعة.',
                'address_en' => 'Demo Nasr City address for radiology catalog QA.',
                'latitude' => 30.0561000,
                'longitude' => 31.3302000,
                'is_main' => true,
                'is_active' => true,
            ],
        );

        $scanRows = [
            ['category' => 'x_ray', 'name_ar' => 'أشعة صدر عادية', 'name_en' => 'Chest X-Ray', 'price' => 250, 'duration' => 15, 'prep' => false],
            ['category' => 'ultrasound', 'name_ar' => 'سونار بطن وحوض', 'name_en' => 'Abdominal and Pelvic Ultrasound', 'price' => 450, 'duration' => 25, 'prep' => true],
            ['category' => 'ct_scan', 'name_ar' => 'أشعة مقطعية على المخ', 'name_en' => 'CT Brain', 'price' => 1300, 'duration' => 30, 'prep' => false],
            ['category' => 'mri', 'name_ar' => 'رنين مغناطيسي على الركبة', 'name_en' => 'Knee MRI', 'price' => 2200, 'duration' => 45, 'prep' => false],
            ['category' => 'mammography', 'name_ar' => 'ماموجرام', 'name_en' => 'Mammography', 'price' => 900, 'duration' => 30, 'prep' => true],
            ['category' => 'doppler', 'name_ar' => 'دوبلر أوردة الساق', 'name_en' => 'Leg Vein Doppler', 'price' => 750, 'duration' => 30, 'prep' => false],
            ['category' => 'echo', 'name_ar' => 'إيكو على القلب', 'name_en' => 'Echocardiography', 'price' => 650, 'duration' => 25, 'prep' => false],
        ];

        foreach ($scanRows as $row) {
            $category = RadiologyScanCategory::query()->where('code', $row['category'])->firstOrFail();
            $scan = RadiologyScan::query()->updateOrCreate(
                ['provider_id' => $provider->id, 'name_en' => $row['name_en']],
                [
                    'branch_id' => $branch->id,
                    'radiology_scan_category_id' => $category->id,
                    'name_ar' => $row['name_ar'],
                    'description_ar' => 'فحص تجريبي لفهرس الأشعة في بيئة local/staging فقط.',
                    'description_en' => 'Demo radiology catalog scan for local/staging QA only.',
                    'preparation_ar' => $row['prep'] ? 'اتبع تعليمات المركز قبل الحضور.' : null,
                    'preparation_en' => $row['prep'] ? 'Follow the center instructions before arrival.' : null,
                    'duration_minutes' => $row['duration'],
                    'base_price' => $row['price'],
                    'requires_preparation' => $row['prep'],
                    'requires_fasting' => $row['category'] === 'ultrasound',
                    'contrast_required' => false,
                    'home_available' => false,
                    'branch_available' => true,
                    'report_delivery_enabled' => true,
                    'is_active' => true,
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                ],
            );

            RadiologyPreparationInstruction::query()->updateOrCreate(
                ['radiology_scan_id' => $scan->id, 'title_en' => $row['name_en'].' preparation'],
                [
                    'radiology_scan_category_id' => $category->id,
                    'title_ar' => 'تعليمات عامة قبل '.$row['name_ar'],
                    'body_ar' => 'يرجى التواصل مع المركز لتأكيد أي تعليمات خاصة قبل موعد الفحص.',
                    'body_en' => 'Please contact the center to confirm any special instructions before the scan appointment.',
                    'warning_ar' => null,
                    'warning_en' => null,
                    'is_active' => true,
                    'sort_order' => 10,
                ],
            );
        }

        return $provider;
    }

    private function seedFitness(User $gymUser, User $fitnessCoachUser, User $nutritionCoachUser, User $admin, City $city, Area $area): void
    {
        $gym = $this->provider(
            type: ProviderType::Gym,
            owner: $gymUser,
            admin: $admin,
            slug: 'pilot-demo-gym',
            nameAr: 'جيم اطمن',
            nameEn: 'Etamen Gym',
            phone: '01000007001',
            descriptionAr: 'جيم تجريبي آمن لاختبار حجز الاشتراكات والحصص داخل البيئة المحلية فقط.',
            descriptionEn: 'Safe demo gym for local membership and class booking QA only.',
        );
        $this->providerStaff($gym, $gymUser, ProviderStaffRole::Owner);

        GymProfile::query()->updateOrCreate(
            ['provider_id' => $gym->id],
            [
                'men_allowed' => true,
                'women_allowed' => true,
                'ladies_only_hours' => false,
                'has_classes' => true,
                'has_personal_training' => true,
                'description_ar' => 'ملف جيم تجريبي بدون أي ادعاءات طبية.',
                'description_en' => 'Demo gym profile without medical claims.',
                'is_active' => true,
            ],
        );

        $gymBranch = ProviderBranch::query()->updateOrCreate(
            ['provider_id' => $gym->id, 'name_en' => 'Etamen Gym Nasr City'],
            [
                'city_id' => $city->id,
                'area_id' => $area->id,
                'name_ar' => 'جيم اطمن - مدينة نصر',
                'phone' => '01000007001',
                'whatsapp' => '01000007001',
                'address_line_1' => 'Demo Gym Street',
                'district' => 'Nasr City',
                'address_ar' => 'شارع تجريبي، مدينة نصر، القاهرة',
                'address_en' => 'Demo Gym Street, Nasr City, Cairo',
                'latitude' => 30.0561000,
                'longitude' => 31.3300000,
                'is_main' => true,
                'is_active' => true,
            ],
        );

        GymMembershipPlan::query()->updateOrCreate(
            ['provider_id' => $gym->id, 'name_en' => 'Monthly Demo Membership'],
            [
                'branch_id' => $gymBranch->id,
                'name_ar' => 'اشتراك شهري',
                'description_ar' => 'اشتراك تجريبي محلي لمدة شهر.',
                'description_en' => 'Local demo monthly membership.',
                'duration_days' => 30,
                'price' => 900,
                'sessions_count' => null,
                'includes_classes' => true,
                'includes_personal_training' => false,
                'is_active' => true,
                'sort_order' => 10,
            ],
        );

        GymMembershipPlan::query()->updateOrCreate(
            ['provider_id' => $gym->id, 'name_en' => '12 Sessions Demo Pass'],
            [
                'branch_id' => $gymBranch->id,
                'name_ar' => 'باقة 12 حصة',
                'description_ar' => 'باقة تجريبية لحجز حصص الجيم.',
                'description_en' => 'Demo 12-session pass.',
                'duration_days' => 45,
                'price' => 1200,
                'sessions_count' => 12,
                'includes_classes' => true,
                'includes_personal_training' => false,
                'is_active' => true,
                'sort_order' => 20,
            ],
        );

        $fitnessCoach = $this->seedCoachProvider(
            user: $fitnessCoachUser,
            admin: $admin,
            type: ProviderType::FitnessCoach,
            coachType: CoachType::Fitness,
            slug: 'pilot-demo-fitness-coach',
            nameAr: 'كابتن أحمد التجريبي',
            nameEn: 'Captain Ahmed Demo',
            phone: '01000007002',
            sessionOneAr: 'جلسة تقييم لياقة',
            sessionOneEn: 'Fitness Assessment Session',
            sessionTwoAr: 'متابعة شهرية',
            sessionTwoEn: 'Monthly Follow-up',
        );

        $nutritionCoach = $this->seedCoachProvider(
            user: $nutritionCoachUser,
            admin: $admin,
            type: ProviderType::NutritionCoach,
            coachType: CoachType::Nutrition,
            slug: 'pilot-demo-nutrition-coach',
            nameAr: 'د. تغذية تجريبي',
            nameEn: 'Demo Nutrition Coach',
            phone: '01000007003',
            sessionOneAr: 'جلسة نظام غذائي',
            sessionOneEn: 'Nutrition Plan Session',
            sessionTwoAr: 'متابعة أسبوعية',
            sessionTwoEn: 'Weekly Follow-up',
        );

        GymClassModel::query()->updateOrCreate(
            ['provider_id' => $gym->id, 'name_en' => 'Cardio Demo Class'],
            [
                'branch_id' => $gymBranch->id,
                'coach_provider_id' => $fitnessCoach->id,
                'name_ar' => 'كارديو',
                'description_ar' => 'حصة كارديو تجريبية بدون أي ادعاء علاجي.',
                'description_en' => 'Demo cardio class without treatment claims.',
                'starts_at' => now()->addDays(2)->setTime(18, 0),
                'ends_at' => now()->addDays(2)->setTime(19, 0),
                'capacity' => 20,
                'price' => 120,
                'is_active' => true,
            ],
        );

        GymClassModel::query()->updateOrCreate(
            ['provider_id' => $gym->id, 'name_en' => 'Strength Demo Class'],
            [
                'branch_id' => $gymBranch->id,
                'coach_provider_id' => $fitnessCoach->id,
                'name_ar' => 'قوة ولياقة',
                'description_ar' => 'حصة قوة ولياقة تجريبية للواجهة المحلية فقط.',
                'description_en' => 'Demo strength class for local QA only.',
                'starts_at' => now()->addDays(3)->setTime(19, 0),
                'ends_at' => now()->addDays(3)->setTime(20, 0),
                'capacity' => 15,
                'price' => 150,
                'is_active' => true,
            ],
        );
    }

    private function seedCoachProvider(
        User $user,
        User $admin,
        ProviderType $type,
        CoachType $coachType,
        string $slug,
        string $nameAr,
        string $nameEn,
        string $phone,
        string $sessionOneAr,
        string $sessionOneEn,
        string $sessionTwoAr,
        string $sessionTwoEn,
    ): Provider {
        $provider = $this->provider(
            type: $type,
            owner: $user,
            admin: $admin,
            slug: $slug,
            nameAr: $nameAr,
            nameEn: $nameEn,
            phone: $phone,
            descriptionAr: 'مقدم خدمة تجريبي للمتابعة الرياضية أو الغذائية بدون تشخيص أو وصفة علاجية.',
            descriptionEn: 'Demo coach provider for fitness/nutrition QA without diagnosis or prescriptions.',
        );
        $this->providerStaff($provider, $user, ProviderStaffRole::Owner);

        CoachProfile::query()->updateOrCreate(
            ['provider_id' => $provider->id],
            [
                'coach_type' => $coachType,
                'experience_years' => $coachType === CoachType::Nutrition ? 7 : 9,
                'session_price' => $coachType === CoachType::Nutrition ? 350 : 250,
                'monthly_followup_price' => $coachType === CoachType::Nutrition ? 900 : 750,
                'online_coaching_enabled' => true,
                'gym_visit_enabled' => $coachType !== CoachType::Nutrition,
                'home_training_enabled' => $coachType !== CoachType::Nutrition,
                'certifications_summary' => 'Demo certification summary visible for local QA only.',
                'is_active' => true,
            ],
        );

        CoachSessionType::query()->updateOrCreate(
            ['provider_id' => $provider->id, 'name_en' => $sessionOneEn],
            [
                'name_ar' => $sessionOneAr,
                'description_ar' => 'جلسة تجريبية للإرشاد العام والمتابعة فقط، وليست وصفة طبية.',
                'description_en' => 'Demo guidance/follow-up session, not medical prescription.',
                'duration_minutes' => 45,
                'price' => $coachType === CoachType::Nutrition ? 350 : 250,
                'session_mode' => CoachSessionMode::Online,
                'is_active' => true,
                'sort_order' => 10,
            ],
        );

        CoachSessionType::query()->updateOrCreate(
            ['provider_id' => $provider->id, 'name_en' => $sessionTwoEn],
            [
                'name_ar' => $sessionTwoAr,
                'description_ar' => 'متابعة تجريبية آمنة للواجهة المحلية فقط.',
                'description_en' => 'Safe demo follow-up for local QA only.',
                'duration_minutes' => 30,
                'price' => $coachType === CoachType::Nutrition ? 180 : 150,
                'session_mode' => CoachSessionMode::Online,
                'is_active' => true,
                'sort_order' => 20,
            ],
        );

        CoachPackage::query()->updateOrCreate(
            ['provider_id' => $provider->id, 'name_en' => $nameEn.' Demo Package'],
            [
                'name_ar' => 'باقة متابعة تجريبية',
                'description_ar' => 'باقة تجريبية للمتابعة فقط بدون ادعاءات علاجية.',
                'description_en' => 'Demo follow-up package without treatment claims.',
                'sessions_count' => 4,
                'duration_days' => 30,
                'price' => $coachType === CoachType::Nutrition ? 950 : 800,
                'is_active' => true,
            ],
        );

        for ($day = 1; $day <= 5; $day++) {
            CoachAvailabilitySlot::query()->updateOrCreate(
                [
                    'provider_id' => $provider->id,
                    'starts_at' => now()->addDays($day)->setTime(17, 0),
                ],
                [
                    'ends_at' => now()->addDays($day)->setTime(18, 0),
                    'status' => CoachAvailabilityStatus::Available,
                ],
            );
        }

        return $provider;
    }

    private function provider(
        ProviderType $type,
        User $owner,
        User $admin,
        string $slug,
        string $nameAr,
        string $nameEn,
        string $phone,
        string $descriptionAr,
        string $descriptionEn,
    ): Provider {
        return Provider::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'type' => $type,
                'owner_user_id' => $owner->id,
                'name_ar' => $nameAr,
                'name_en' => $nameEn,
                'phone' => $phone,
                'email' => $owner->email,
                'description_ar' => $descriptionAr,
                'description_en' => $descriptionEn,
                'status' => ProviderStatus::Approved,
                'is_active' => true,
                'approved_at' => now(),
                'rejected_at' => null,
                'suspended_at' => null,
                'created_by' => $admin->id,
                'reviewed_by' => $admin->id,
                'metadata' => ['pilot_demo' => true],
            ],
        );
    }

    private function seedAdminOperationsDemo(User $admin, User $patient, Provider $doctorProvider, Provider $pharmacyProvider, Provider $labProvider): void
    {
        $radiologyProvider = Provider::query()->where('slug', 'pilot-demo-radiology')->first();
        $gymProvider = Provider::query()->where('slug', 'pilot-demo-gym')->first();
        $coachProvider = Provider::query()->where('slug', 'pilot-demo-fitness-coach')->first();

        $this->seedPendingPaymentReview($patient, $doctorProvider, 'doctor', 300, 'admin-demo-doctor-payment');
        if ($radiologyProvider) {
            $this->seedPendingPaymentReview($patient, $radiologyProvider, 'radiology', 450, 'admin-demo-radiology-payment');
        }
        if ($gymProvider) {
            $this->seedPendingPaymentReview($patient, $gymProvider, 'gym', 900, 'admin-demo-gym-payment');
        }
        $this->seedPendingPaymentReview($patient, $pharmacyProvider, 'pharmacy', 160, 'admin-demo-pharmacy-payment');
        $this->seedPendingPaymentReview($patient, $labProvider, 'lab', 180, 'admin-demo-lab-payment');

        foreach ([
            [
                'email' => 'pilot.pending.hospital@example.test',
                'slug' => 'pilot-pending-hospital',
                'type' => ProviderType::Hospital,
                'name_ar' => 'مستشفى تجريبية قيد المراجعة',
                'name_en' => 'Pending Demo Hospital',
            ],
            [
                'email' => 'pilot.pending.radiology@example.test',
                'slug' => 'pilot-pending-radiology',
                'type' => ProviderType::Radiology,
                'name_ar' => 'مركز أشعة تجريبي قيد المراجعة',
                'name_en' => 'Pending Demo Radiology',
            ],
        ] as $row) {
            $owner = $this->demoUser($row['email'], $row['name_en'].' Owner', UserRole::ProviderAdmin);
            $provider = Provider::query()->updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'type' => $row['type'],
                    'owner_user_id' => $owner->id,
                    'name_ar' => $row['name_ar'],
                    'name_en' => $row['name_en'],
                    'phone' => '01000999000',
                    'email' => $owner->email,
                    'description_ar' => 'مزود تجريبي محلي لاختبار قائمة موافقات الإدارة.',
                    'description_en' => 'Local demo provider for admin approval queue.',
                    'status' => ProviderStatus::PendingReview,
                    'is_active' => false,
                    'approved_at' => null,
                    'rejected_at' => null,
                    'suspended_at' => null,
                    'created_by' => $admin->id,
                    'reviewed_by' => null,
                    'metadata' => ['pilot_demo' => true, 'admin_operations_demo' => true],
                ],
            );
            $this->providerStaff($provider, $owner, ProviderStaffRole::Owner);
            ProviderApprovalRequest::query()->updateOrCreate(
                ['provider_id' => $provider->id, 'requested_by' => $owner->id],
                [
                    'status' => ApprovalRequestStatus::Pending,
                    'notes' => 'Local demo approval request for Sprint 58.',
                    'review_notes' => null,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                ],
            );
        }

        $ticketRows = [
            ['ticket_number' => 'SUP-DEMO-001', 'provider_id' => $doctorProvider->id, 'category' => SupportTicket::CATEGORY_PAYMENT, 'subject' => 'مراجعة إثبات دفع تجريبي'],
            ['ticket_number' => 'SUP-DEMO-002', 'provider_id' => $pharmacyProvider->id, 'category' => SupportTicket::CATEGORY_PROVIDER, 'subject' => 'استفسار مزود تجريبي'],
            ['ticket_number' => 'SUP-DEMO-003', 'provider_id' => $labProvider->id, 'category' => SupportTicket::CATEGORY_TECHNICAL, 'subject' => 'مشكلة تقنية تجريبية'],
        ];

        foreach ($ticketRows as $row) {
            $ticket = SupportTicket::query()->updateOrCreate(
                ['ticket_number' => $row['ticket_number']],
                [
                    'user_id' => $patient->id,
                    'provider_id' => $row['provider_id'],
                    'category' => $row['category'],
                    'subject' => $row['subject'],
                    'description' => 'تذكرة دعم تجريبية محلية لمركز عمليات الإدارة.',
                    'status' => SupportTicket::STATUS_OPEN,
                    'priority' => 'normal',
                    'source' => 'seed',
                    'assigned_admin_id' => $admin->id,
                    'closed_at' => null,
                ],
            );

            $ticket->messages()->updateOrCreate(
                ['sender_user_id' => $patient->id, 'sender_type' => 'patient', 'message' => $ticket->description],
                ['is_internal_note' => false],
            );
        }

        foreach ([1 => 150, 2 => 300] as $index => $amount) {
            RefundRequest::query()->updateOrCreate(
                ['refund_number' => 'REF-DEMO-00'.$index],
                [
                    'user_id' => $patient->id,
                    'payment_id' => null,
                    'context_type' => 'demo',
                    'context_id' => $index,
                    'amount' => $amount,
                    'currency' => 'EGP',
                    'reason' => 'طلب استرداد تجريبي محلي.',
                    'status' => $index === 1 ? RefundRequest::STATUS_REQUESTED : RefundRequest::STATUS_UNDER_REVIEW,
                    'admin_note' => null,
                    'resolved_by' => null,
                    'resolved_at' => null,
                ],
            );

            Dispute::query()->updateOrCreate(
                ['dispute_number' => 'DSP-DEMO-00'.$index],
                [
                    'user_id' => $patient->id,
                    'provider_id' => $index === 1 ? $doctorProvider->id : $pharmacyProvider->id,
                    'payment_id' => null,
                    'context_type' => 'demo',
                    'context_id' => $index,
                    'reason' => 'نزاع تجريبي محلي لفحص مركز العمليات.',
                    'status' => $index === 1 ? Dispute::STATUS_OPEN : Dispute::STATUS_INVESTIGATING,
                    'priority' => 'normal',
                    'assigned_admin_id' => $admin->id,
                    'resolved_at' => null,
                ],
            );
        }
    }

    private function seedPendingPaymentReview(User $patient, Provider $provider, string $providerType, int $amount, string $seedKey): void
    {
        $method = PaymentMethod::query()
            ->where('type', PaymentMethodType::ManualVodafoneCash)
            ->first();

        if (! $method) {
            return;
        }

        $payment = Payment::query()
            ->get()
            ->first(fn (Payment $payment): bool => ($payment->metadata['seed_key'] ?? null) === $seedKey);

        if (! $payment) {
            $payment = new Payment;
        }

        $payment->forceFill([
            'payable_type' => null,
            'payable_id' => null,
            'user_id' => $patient->id,
            'provider_id' => $provider->id,
            'provider_type' => $providerType,
            'payment_method_id' => $method->id,
            'amount' => $amount,
            'currency' => 'EGP',
            'status' => PaymentStatus::PendingReview,
            'created_by' => $patient->id,
            'reviewed_by' => null,
            'metadata' => ['pilot_demo' => true, 'seed_key' => $seedKey],
        ])->save();

        $file = UploadedFile::query()->updateOrCreate(
            ['path' => 'payment-proofs/'.$seedKey.'.jpg'],
            [
                'owner_type' => Payment::class,
                'owner_id' => $payment->id,
                'uploaded_by' => $patient->id,
                'disk' => 'medical_private',
                'original_name' => $seedKey.'.jpg',
                'mime_type' => 'image/jpeg',
                'size' => 12800,
                'file_category' => FileCategory::PaymentProof,
                'visibility' => FileVisibility::Private,
                'checksum' => hash('sha256', $seedKey),
                'metadata' => ['pilot_demo' => true],
            ],
        );

        PaymentProof::query()->updateOrCreate(
            ['payment_id' => $payment->id, 'file_id' => $file->id],
            [
                'uploaded_by' => $patient->id,
                'reference_number' => strtoupper($seedKey),
                'sender_phone' => '01000000001',
                'notes' => 'Local demo payment proof for admin operations.',
                'status' => PaymentProofStatus::PendingReview,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'rejection_reason' => null,
            ],
        );
    }

    private function providerStaff(Provider $provider, User $user, ProviderStaffRole $role, ?array $permissions = null): void
    {
        ProviderStaff::query()->updateOrCreate(
            ['provider_id' => $provider->id, 'user_id' => $user->id],
            [
                'role' => $role,
                'is_owner' => $role === ProviderStaffRole::Owner,
                'status' => 'active',
                'permissions' => $permissions,
            ],
        );
    }
}
