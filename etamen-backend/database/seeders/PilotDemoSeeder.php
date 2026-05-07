<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Appointments\Domain\Enums\AppointmentSlotStatus;
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
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyProduct;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\DoctorProfile;
use App\Modules\Providers\Infrastructure\Models\LabProfile;
use App\Modules\Providers\Infrastructure\Models\PharmacyProfile;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderBranch;
use App\Modules\Providers\Infrastructure\Models\ProviderStaff;
use App\Modules\Providers\Infrastructure\Models\Specialty;
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

            $this->seedPatientProfile($patient);
            [$city, $area] = $this->seedLocation();
            [$doctorProvider, $doctorProfile, $branch] = $this->seedDoctor($doctorUser, $admin, $city, $area);
            $this->seedDoctorScheduleAndSlots($doctorProvider, $doctorProfile, $branch);
            $this->seedPaymentMethods();
            $pharmacyProvider = $this->seedPharmacy($pharmacyUser, $admin, $city, $area);
            $labProvider = $this->seedLab($labUser, $admin, $city, $area);
            $this->seedHealthData($patient);
            $this->seedMedicationData($patient);
            $this->seedCarePlan($patient, $admin, $doctorProvider);
            $this->seedNotification($patient);
            $this->seedDemoLabResultOrder($patient, $labUser, $labProvider);
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

    private function providerStaff(Provider $provider, User $user, ProviderStaffRole $role): void
    {
        ProviderStaff::query()->updateOrCreate(
            ['provider_id' => $provider->id, 'user_id' => $user->id],
            [
                'role' => $role,
                'is_owner' => $role === ProviderStaffRole::Owner,
                'status' => 'active',
            ],
        );
    }
}
