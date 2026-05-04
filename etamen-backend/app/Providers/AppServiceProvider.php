<?php

namespace App\Providers;

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
    }
}
