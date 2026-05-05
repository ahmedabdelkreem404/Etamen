<?php

namespace App\Providers;

use App\Modules\AI\Infrastructure\Models\AiConversation;
use App\Modules\AI\Infrastructure\Models\AiMessage;
use App\Modules\AI\Infrastructure\Models\AiProviderConfig;
use App\Modules\AI\Infrastructure\Models\AiSafetyEvent;
use App\Modules\AI\Infrastructure\Models\AiUsageLog;
use App\Modules\AI\Policies\AiConversationPolicy;
use App\Modules\AI\Policies\AiMessagePolicy;
use App\Modules\AI\Policies\AiProviderConfigPolicy;
use App\Modules\AI\Policies\AiSafetyEventPolicy;
use App\Modules\AI\Policies\AiUsageLogPolicy;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\Appointments\Infrastructure\Models\AppointmentReview;
use App\Modules\Appointments\Infrastructure\Models\AppointmentSlot;
use App\Modules\Appointments\Infrastructure\Models\DoctorHoliday;
use App\Modules\Appointments\Infrastructure\Models\DoctorSchedule;
use App\Modules\Appointments\Infrastructure\Models\DoctorScheduleDay;
use App\Modules\Appointments\Policies\AppointmentPolicy;
use App\Modules\Appointments\Policies\AppointmentReviewPolicy;
use App\Modules\Appointments\Policies\AppointmentSlotPolicy;
use App\Modules\Appointments\Policies\DoctorHolidayPolicy;
use App\Modules\Appointments\Policies\DoctorScheduleDayPolicy;
use App\Modules\Appointments\Policies\DoctorSchedulePolicy;
use App\Modules\CarePlans\Infrastructure\Models\CarePlan;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanCheckin;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanDay;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanFoodItem;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanInstruction;
use App\Modules\CarePlans\Infrastructure\Models\CarePlanMeal;
use App\Modules\CarePlans\Infrastructure\Models\MealLog;
use App\Modules\CarePlans\Policies\CarePlanCheckinPolicy;
use App\Modules\CarePlans\Policies\CarePlanDayPolicy;
use App\Modules\CarePlans\Policies\CarePlanFoodItemPolicy;
use App\Modules\CarePlans\Policies\CarePlanInstructionPolicy;
use App\Modules\CarePlans\Policies\CarePlanMealPolicy;
use App\Modules\CarePlans\Policies\CarePlanPolicy;
use App\Modules\CarePlans\Policies\MealLogPolicy;
use App\Modules\Health\Infrastructure\Models\HealthAccessLog;
use App\Modules\Health\Infrastructure\Models\HealthGoal;
use App\Modules\Health\Infrastructure\Models\HealthProfile;
use App\Modules\Health\Infrastructure\Models\PatientAllergy;
use App\Modules\Health\Infrastructure\Models\PatientChronicDisease;
use App\Modules\Health\Infrastructure\Models\PatientCurrentMedication;
use App\Modules\Health\Infrastructure\Models\PatientSurgery;
use App\Modules\Health\Infrastructure\Models\VitalRecord;
use App\Modules\Health\Policies\HealthAccessLogPolicy;
use App\Modules\Health\Policies\HealthGoalPolicy;
use App\Modules\Health\Policies\HealthProfilePolicy;
use App\Modules\Health\Policies\PatientAllergyPolicy;
use App\Modules\Health\Policies\PatientChronicDiseasePolicy;
use App\Modules\Health\Policies\PatientCurrentMedicationPolicy;
use App\Modules\Health\Policies\PatientSurgeryPolicy;
use App\Modules\Health\Policies\VitalRecordPolicy;
use App\Modules\Labs\Infrastructure\Models\LabOrder;
use App\Modules\Labs\Infrastructure\Models\LabPackage;
use App\Modules\Labs\Infrastructure\Models\LabResult;
use App\Modules\Labs\Infrastructure\Models\LabTest;
use App\Modules\Labs\Policies\LabOrderPolicy;
use App\Modules\Labs\Policies\LabPackagePolicy;
use App\Modules\Labs\Policies\LabResultPolicy;
use App\Modules\Labs\Policies\LabTestPolicy;
use App\Modules\Medications\Infrastructure\Models\MedicationLog;
use App\Modules\Medications\Infrastructure\Models\MedicationNotificationQueue;
use App\Modules\Medications\Infrastructure\Models\MedicationRefillEvent;
use App\Modules\Medications\Infrastructure\Models\MedicationReminder;
use App\Modules\Medications\Infrastructure\Models\MedicationReminderTime;
use App\Modules\Medications\Policies\MedicationLogPolicy;
use App\Modules\Medications\Policies\MedicationNotificationQueuePolicy;
use App\Modules\Medications\Policies\MedicationRefillEventPolicy;
use App\Modules\Medications\Policies\MedicationReminderPolicy;
use App\Modules\Medications\Policies\MedicationReminderTimePolicy;
use App\Modules\Notifications\Infrastructure\Models\Notification;
use App\Modules\Notifications\Infrastructure\Models\NotificationDispatch;
use App\Modules\Notifications\Infrastructure\Models\NotificationPreference;
use App\Modules\Notifications\Infrastructure\Models\NotificationTemplate;
use App\Modules\Notifications\Infrastructure\Models\NotificationToken;
use App\Modules\Notifications\Infrastructure\Models\SchedulerRun;
use App\Modules\Notifications\Policies\NotificationDispatchPolicy;
use App\Modules\Notifications\Policies\NotificationPolicy;
use App\Modules\Notifications\Policies\NotificationPreferencePolicy;
use App\Modules\Notifications\Policies\NotificationTemplatePolicy;
use App\Modules\Notifications\Policies\NotificationTokenPolicy;
use App\Modules\Notifications\Policies\SchedulerRunPolicy;
use App\Modules\Payments\Infrastructure\Models\Invoice;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Payments\Infrastructure\Models\PaymentProof;
use App\Modules\Payments\Policies\InvoicePolicy;
use App\Modules\Payments\Policies\PaymentPolicy;
use App\Modules\Payments\Policies\PaymentProofPolicy;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyOrder;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyPrescription;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyProduct;
use App\Modules\Pharmacies\Policies\PharmacyOrderPolicy;
use App\Modules\Pharmacies\Policies\PharmacyPrescriptionPolicy;
use App\Modules\Pharmacies\Policies\PharmacyProductPolicy;
use App\Modules\Providers\Infrastructure\Models\DoctorProfile;
use App\Modules\Providers\Infrastructure\Models\LabProfile;
use App\Modules\Providers\Infrastructure\Models\PharmacyProfile;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderBranch;
use App\Modules\Providers\Infrastructure\Models\ProviderDocument;
use App\Modules\Providers\Infrastructure\Models\Specialty;
use App\Modules\Providers\Policies\DoctorProfilePolicy;
use App\Modules\Providers\Policies\LabProfilePolicy;
use App\Modules\Providers\Policies\PharmacyProfilePolicy;
use App\Modules\Providers\Policies\ProviderBranchPolicy;
use App\Modules\Providers\Policies\ProviderDocumentPolicy;
use App\Modules\Providers\Policies\ProviderPolicy;
use App\Modules\Providers\Policies\SpecialtyPolicy;
use App\Modules\Wallets\Infrastructure\Models\CommissionRule;
use App\Modules\Wallets\Infrastructure\Models\Settlement;
use App\Modules\Wallets\Infrastructure\Models\Wallet;
use App\Modules\Wallets\Infrastructure\Models\WalletTransaction;
use App\Modules\Wallets\Infrastructure\Models\WithdrawalRequest;
use App\Modules\Wallets\Policies\CommissionRulePolicy;
use App\Modules\Wallets\Policies\SettlementPolicy;
use App\Modules\Wallets\Policies\WalletPolicy;
use App\Modules\Wallets\Policies\WalletTransactionPolicy;
use App\Modules\Wallets\Policies\WithdrawalRequestPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(glob(app_path('Modules/*/Database/Migrations'), GLOB_ONLYDIR));
        $this->configureRateLimiters();

        Gate::policy(Provider::class, ProviderPolicy::class);
        Gate::policy(ProviderBranch::class, ProviderBranchPolicy::class);
        Gate::policy(ProviderDocument::class, ProviderDocumentPolicy::class);
        Gate::policy(DoctorProfile::class, DoctorProfilePolicy::class);
        Gate::policy(PharmacyProfile::class, PharmacyProfilePolicy::class);
        Gate::policy(LabProfile::class, LabProfilePolicy::class);
        Gate::policy(Specialty::class, SpecialtyPolicy::class);
        Gate::policy(DoctorSchedule::class, DoctorSchedulePolicy::class);
        Gate::policy(DoctorScheduleDay::class, DoctorScheduleDayPolicy::class);
        Gate::policy(DoctorHoliday::class, DoctorHolidayPolicy::class);
        Gate::policy(AppointmentSlot::class, AppointmentSlotPolicy::class);
        Gate::policy(Appointment::class, AppointmentPolicy::class);
        Gate::policy(AppointmentReview::class, AppointmentReviewPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(PaymentProof::class, PaymentProofPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(PharmacyProduct::class, PharmacyProductPolicy::class);
        Gate::policy(PharmacyPrescription::class, PharmacyPrescriptionPolicy::class);
        Gate::policy(PharmacyOrder::class, PharmacyOrderPolicy::class);
        Gate::policy(LabTest::class, LabTestPolicy::class);
        Gate::policy(LabPackage::class, LabPackagePolicy::class);
        Gate::policy(LabOrder::class, LabOrderPolicy::class);
        Gate::policy(LabResult::class, LabResultPolicy::class);
        Gate::policy(HealthProfile::class, HealthProfilePolicy::class);
        Gate::policy(PatientChronicDisease::class, PatientChronicDiseasePolicy::class);
        Gate::policy(PatientAllergy::class, PatientAllergyPolicy::class);
        Gate::policy(PatientCurrentMedication::class, PatientCurrentMedicationPolicy::class);
        Gate::policy(PatientSurgery::class, PatientSurgeryPolicy::class);
        Gate::policy(HealthGoal::class, HealthGoalPolicy::class);
        Gate::policy(VitalRecord::class, VitalRecordPolicy::class);
        Gate::policy(HealthAccessLog::class, HealthAccessLogPolicy::class);
        Gate::policy(MedicationReminder::class, MedicationReminderPolicy::class);
        Gate::policy(MedicationReminderTime::class, MedicationReminderTimePolicy::class);
        Gate::policy(MedicationLog::class, MedicationLogPolicy::class);
        Gate::policy(MedicationRefillEvent::class, MedicationRefillEventPolicy::class);
        Gate::policy(MedicationNotificationQueue::class, MedicationNotificationQueuePolicy::class);
        Gate::policy(CarePlan::class, CarePlanPolicy::class);
        Gate::policy(CarePlanDay::class, CarePlanDayPolicy::class);
        Gate::policy(CarePlanMeal::class, CarePlanMealPolicy::class);
        Gate::policy(CarePlanFoodItem::class, CarePlanFoodItemPolicy::class);
        Gate::policy(CarePlanInstruction::class, CarePlanInstructionPolicy::class);
        Gate::policy(CarePlanCheckin::class, CarePlanCheckinPolicy::class);
        Gate::policy(MealLog::class, MealLogPolicy::class);
        Gate::policy(AiConversation::class, AiConversationPolicy::class);
        Gate::policy(AiMessage::class, AiMessagePolicy::class);
        Gate::policy(AiSafetyEvent::class, AiSafetyEventPolicy::class);
        Gate::policy(AiUsageLog::class, AiUsageLogPolicy::class);
        Gate::policy(AiProviderConfig::class, AiProviderConfigPolicy::class);
        Gate::policy(Notification::class, NotificationPolicy::class);
        Gate::policy(NotificationToken::class, NotificationTokenPolicy::class);
        Gate::policy(NotificationPreference::class, NotificationPreferencePolicy::class);
        Gate::policy(NotificationTemplate::class, NotificationTemplatePolicy::class);
        Gate::policy(NotificationDispatch::class, NotificationDispatchPolicy::class);
        Gate::policy(SchedulerRun::class, SchedulerRunPolicy::class);
        Gate::policy(Wallet::class, WalletPolicy::class);
        Gate::policy(WalletTransaction::class, WalletTransactionPolicy::class);
        Gate::policy(CommissionRule::class, CommissionRulePolicy::class);
        Gate::policy(WithdrawalRequest::class, WithdrawalRequestPolicy::class);
        Gate::policy(Settlement::class, SettlementPolicy::class);
    }

    private function configureRateLimiters(): void
    {
        RateLimiter::for('auth-sensitive', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('sensitive-action', fn (Request $request) => Limit::perMinute(30)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('file-upload', fn (Request $request) => Limit::perMinute(12)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('booking', fn (Request $request) => Limit::perMinute(20)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('health-write', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('ai-message', fn (Request $request) => Limit::perMinute(30)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('notification-write', fn (Request $request) => Limit::perMinute(30)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('admin-sensitive', fn (Request $request) => Limit::perMinute(120)->by($request->user()?->id ?: $request->ip()));
    }
}
