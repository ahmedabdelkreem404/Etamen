<?php

namespace App\Providers;

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
use Illuminate\Support\Facades\Gate;
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
    }
}
