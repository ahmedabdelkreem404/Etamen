<?php

namespace App\Modules\Providers\Infrastructure\Models;

use App\Models\User;
use App\Modules\Fitness\Infrastructure\Models\CoachAvailabilitySlot;
use App\Modules\Fitness\Infrastructure\Models\CoachPackage;
use App\Modules\Fitness\Infrastructure\Models\CoachSessionType;
use App\Modules\Fitness\Infrastructure\Models\GymClassModel;
use App\Modules\Fitness\Infrastructure\Models\GymMembershipPlan;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Provider extends Model
{
    protected $fillable = [
        'type',
        'owner_user_id',
        'name_ar',
        'name_en',
        'slug',
        'phone',
        'email',
        'description_ar',
        'description_en',
        'status',
        'is_active',
        'approved_at',
        'rejected_at',
        'suspended_at',
        'created_by',
        'reviewed_by',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProviderType::class,
            'status' => ProviderStatus::class,
            'is_active' => 'boolean',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'suspended_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Provider $provider): void {
            if (! $provider->slug) {
                $provider->slug = static::uniqueSlug($provider->name_en);
            }
        });
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('status', ProviderStatus::Approved)
            ->where('is_active', true);
    }

    public function scopePublicDiscoveryEnabled(Builder $query): Builder
    {
        return $query->whereIn('type', ProviderType::publicDiscoveryValues());
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: Str::lower(Str::random(8));
        $slug = $base;
        $counter = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function branches(): HasMany
    {
        return $this->hasMany(ProviderBranch::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(ProviderStaff::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProviderDocument::class);
    }

    public function approvalRequests(): HasMany
    {
        return $this->hasMany(ProviderApprovalRequest::class);
    }

    public function doctorProfile(): HasOne
    {
        return $this->hasOne(DoctorProfile::class);
    }

    public function pharmacyProfile(): HasOne
    {
        return $this->hasOne(PharmacyProfile::class);
    }

    public function labProfile(): HasOne
    {
        return $this->hasOne(LabProfile::class);
    }

    public function hospitalProfile(): HasOne
    {
        return $this->hasOne(HospitalProfile::class);
    }

    public function clinicProfile(): HasOne
    {
        return $this->hasOne(ClinicProfile::class);
    }

    public function medicalCenterProfile(): HasOne
    {
        return $this->hasOne(MedicalCenterProfile::class);
    }

    public function radiologyProfile(): HasOne
    {
        return $this->hasOne(RadiologyProfile::class);
    }

    public function gymProfile(): HasOne
    {
        return $this->hasOne(GymProfile::class);
    }

    public function coachProfile(): HasOne
    {
        return $this->hasOne(CoachProfile::class);
    }

    public function physiotherapyProfile(): HasOne
    {
        return $this->hasOne(PhysiotherapyProfile::class);
    }

    public function homeHealthcareProfile(): HasOne
    {
        return $this->hasOne(HomeHealthcareProfile::class);
    }

    public function bookingSettings(): HasOne
    {
        return $this->hasOne(ProviderBookingSetting::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(ProviderService::class);
    }

    public function publicServices(): HasMany
    {
        return $this->services()->active()->with('category');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(ProviderContract::class);
    }

    public function activeContract(): HasOne
    {
        return $this->hasOne(ProviderContract::class)->active()->latestOfMany();
    }

    public function publicDocuments(): HasMany
    {
        return $this->documents()->publicCertificates()->with('file');
    }

    public function hospitalDepartments(): HasMany
    {
        return $this->hasMany(HospitalDepartment::class, 'hospital_provider_id');
    }

    public function hospitalDoctorLinks(): HasMany
    {
        return $this->hasMany(HospitalDoctor::class, 'hospital_provider_id');
    }

    public function affiliatedHospitalLinks(): HasMany
    {
        return $this->hasMany(HospitalDoctor::class, 'doctor_provider_id');
    }

    public function gymMembershipPlans(): HasMany
    {
        return $this->hasMany(GymMembershipPlan::class);
    }

    public function gymClasses(): HasMany
    {
        return $this->hasMany(GymClassModel::class);
    }

    public function coachSessionTypes(): HasMany
    {
        return $this->hasMany(CoachSessionType::class);
    }

    public function coachAvailabilitySlots(): HasMany
    {
        return $this->hasMany(CoachAvailabilitySlot::class);
    }

    public function coachPackages(): HasMany
    {
        return $this->hasMany(CoachPackage::class);
    }
}
