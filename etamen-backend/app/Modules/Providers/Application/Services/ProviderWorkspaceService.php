<?php

namespace App\Modules\Providers\Application\Services;

use App\Models\User;
use App\Modules\Appointments\Domain\Enums\AppointmentSlotStatus;
use App\Modules\Appointments\Domain\Enums\AppointmentStatus;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\Fitness\Domain\Enums\CoachAvailabilityStatus;
use App\Modules\Fitness\Domain\Enums\CoachBookingStatus;
use App\Modules\Fitness\Domain\Enums\GymBookingStatus;
use App\Modules\Fitness\Infrastructure\Models\CoachAvailabilitySlot;
use App\Modules\Fitness\Infrastructure\Models\CoachBooking;
use App\Modules\Fitness\Infrastructure\Models\CoachPackage;
use App\Modules\Fitness\Infrastructure\Models\CoachSessionType;
use App\Modules\Fitness\Infrastructure\Models\GymBooking;
use App\Modules\Fitness\Infrastructure\Models\GymClassModel;
use App\Modules\Fitness\Infrastructure\Models\GymMembershipPlan;
use App\Modules\Labs\Domain\Enums\LabOrderStatus;
use App\Modules\Labs\Infrastructure\Models\LabOrder;
use App\Modules\Labs\Infrastructure\Models\LabPackage;
use App\Modules\Labs\Infrastructure\Models\LabTest;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderStatus;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyOrder;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyProduct;
use App\Modules\Providers\Domain\Enums\ProviderPermission;
use App\Modules\Providers\Domain\Enums\ProviderStaffRole;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Providers\Infrastructure\Models\HospitalDepartment;
use App\Modules\Providers\Infrastructure\Models\HospitalDoctor;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Providers\Infrastructure\Models\ProviderStaff;
use App\Modules\Radiology\Domain\Enums\RadiologyOrderStatus;
use App\Modules\Radiology\Infrastructure\Models\RadiologyOrder;
use App\Modules\Radiology\Infrastructure\Models\RadiologyScan;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ProviderWorkspaceService
{
    public function workspacesFor(User $user): array
    {
        $providerStaff = $this->activeStaffQuery($user)
            ->with('provider')
            ->get()
            ->filter(fn (ProviderStaff $staff): bool => $this->providerIsDashboardVisible($staff->provider));

        $workspaces = [
            $this->patientWorkspace(),
        ];

        foreach ($providerStaff as $staff) {
            $workspaces[] = $this->providerWorkspace($staff);
        }

        if ($user->isPlatformAdmin()) {
            $workspaces[] = [
                'type' => 'platform_admin',
                'key' => 'platform_admin',
                'label_ar' => 'إدارة المنصة',
                'label_en' => 'Platform Admin',
                'permissions' => ['platform_admin'],
            ];
        }

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'default_workspace' => $workspaces[0]['key'],
            'available_workspaces' => $workspaces,
        ];
    }

    public function dashboardFor(User $user, Provider $provider): array
    {
        $staff = $this->activeStaffFor($user, $provider);
        $permissions = $staff->effectivePermissions();

        $provider->loadMissing('branches.city', 'branches.area');
        $quickActions = $this->quickActionsFor($provider->type, $permissions);

        return [
            'provider' => $this->providerSummary($provider),
            'role' => $staff->role->value,
            'is_owner' => (bool) $staff->is_owner,
            'permissions' => $permissions,
            'today_count' => $this->todayCount($provider),
            'pending_payment_review_count' => $this->pendingPaymentReviewCount($provider),
            'pending_actions_count' => $this->pendingActionsCount($provider),
            'summary_cards' => $this->summaryCards($provider),
            'recent_items' => $this->recentItems($provider),
            'quick_actions' => $quickActions,
        ];
    }

    public function staffList(User $actor, Provider $provider): array
    {
        $this->authorizeManageStaff($actor, $provider);

        return $provider->staff()
            ->with('user')
            ->orderByDesc('is_owner')
            ->orderBy('id')
            ->get()
            ->map(fn (ProviderStaff $staff): array => $this->staffPayload($staff))
            ->all();
    }

    public function addStaff(User $actor, Provider $provider, array $data): array
    {
        $actorStaff = $this->authorizeManageStaff($actor, $provider);
        $targetUser = User::query()->where('email', $data['email'])->first();

        if (! $targetUser) {
            throw ValidationException::withMessages([
                'email' => ['User not found. Invite flow is intentionally deferred.'],
            ]);
        }

        $role = ProviderStaffRole::from($data['role'] ?? ProviderStaffRole::Staff->value);
        if ($role === ProviderStaffRole::Owner) {
            throw ValidationException::withMessages([
                'role' => ['Owner role cannot be granted through this endpoint.'],
            ]);
        }

        if (! $actorStaff->is_owner && $role === ProviderStaffRole::Admin) {
            throw ValidationException::withMessages([
                'role' => ['Only the provider owner can grant admin staff role.'],
            ]);
        }

        $permissions = $this->validatedAssignablePermissions(
            $data['permissions'] ?? null,
            $actorStaff,
            $role,
        );

        $staff = ProviderStaff::query()->updateOrCreate(
            ['provider_id' => $provider->id, 'user_id' => $targetUser->id],
            [
                'role' => $role,
                'is_owner' => false,
                'status' => 'active',
                'permissions' => $permissions,
            ],
        );

        return $this->staffPayload($staff->load('user'));
    }

    public function updateStaff(User $actor, Provider $provider, ProviderStaff $staff, array $data): array
    {
        $actorStaff = $this->authorizeManageStaff($actor, $provider);
        $this->ensureSameProviderStaff($provider, $staff);

        if ($staff->is_owner) {
            throw ValidationException::withMessages([
                'staff' => ['Provider owner permissions cannot be changed through this endpoint.'],
            ]);
        }

        $role = array_key_exists('role', $data)
            ? ProviderStaffRole::from($data['role'])
            : $staff->role;

        if ($role === ProviderStaffRole::Owner) {
            throw ValidationException::withMessages([
                'role' => ['Owner role cannot be granted through this endpoint.'],
            ]);
        }

        if (! $actorStaff->is_owner && $role === ProviderStaffRole::Admin) {
            throw ValidationException::withMessages([
                'role' => ['Only the provider owner can grant admin staff role.'],
            ]);
        }

        $staff->fill([
            'role' => $role,
            'status' => $data['status'] ?? $staff->status,
            'permissions' => $this->validatedAssignablePermissions(
                $data['permissions'] ?? $staff->permissions,
                $actorStaff,
                $role,
            ),
        ])->save();

        return $this->staffPayload($staff->refresh()->load('user'));
    }

    public function deactivateStaff(User $actor, Provider $provider, ProviderStaff $staff): array
    {
        $this->authorizeManageStaff($actor, $provider);
        $this->ensureSameProviderStaff($provider, $staff);

        if ($staff->is_owner) {
            throw ValidationException::withMessages([
                'staff' => ['Provider owner cannot be removed.'],
            ]);
        }

        $staff->forceFill(['status' => 'inactive'])->save();

        return $this->staffPayload($staff->refresh()->load('user'));
    }

    public function activeStaffFor(User $user, Provider $provider): ProviderStaff
    {
        $staff = $this->activeStaffQuery($user)
            ->where('provider_id', $provider->id)
            ->first();

        if (! $staff || ! $this->providerIsDashboardVisible($provider)) {
            throw new AuthorizationException('You are not allowed to access this provider workspace.');
        }

        return $staff;
    }

    public function hasProviderPermission(User $user, Provider $provider, ProviderPermission|string $permission): bool
    {
        try {
            return $this->activeStaffFor($user, $provider)->hasPermission($permission);
        } catch (AuthorizationException) {
            return false;
        }
    }

    private function activeStaffQuery(User $user)
    {
        return ProviderStaff::query()
            ->where('user_id', $user->id)
            ->where('status', 'active');
    }

    private function authorizeManageStaff(User $actor, Provider $provider): ProviderStaff
    {
        $staff = $this->activeStaffFor($actor, $provider);

        if (! $staff->hasPermission(ProviderPermission::ManageStaff)) {
            throw new AuthorizationException('manage_staff permission is required.');
        }

        return $staff;
    }

    private function ensureSameProviderStaff(Provider $provider, ProviderStaff $staff): void
    {
        if ((int) $staff->provider_id !== (int) $provider->id) {
            throw new AuthorizationException('Staff member belongs to another provider.');
        }
    }

    private function providerIsDashboardVisible(?Provider $provider): bool
    {
        return $provider !== null
            && $provider->is_active
            && $provider->status === ProviderStatus::Approved;
    }

    private function patientWorkspace(): array
    {
        return [
            'type' => 'patient',
            'key' => 'patient',
            'label_ar' => 'حسابي كمريض',
            'label_en' => 'My patient account',
            'permissions' => [],
        ];
    }

    private function providerWorkspace(ProviderStaff $staff): array
    {
        $provider = $staff->provider;

        return [
            'type' => 'provider',
            'key' => 'provider:'.$provider->id,
            'provider_id' => $provider->id,
            'provider_type' => $provider->type->value,
            'provider_name_ar' => $provider->name_ar,
            'provider_name_en' => $provider->name_en,
            'role' => $staff->role->value,
            'is_owner' => (bool) $staff->is_owner,
            'permissions' => $staff->effectivePermissions(),
            'status' => $provider->is_active ? 'active' : 'inactive',
        ];
    }

    private function providerSummary(Provider $provider): array
    {
        $mainBranch = $provider->branches->sortByDesc('is_main')->first();

        return [
            'id' => $provider->id,
            'type' => $provider->type->value,
            'name_ar' => $provider->name_ar,
            'name_en' => $provider->name_en,
            'status' => $provider->status->value,
            'is_active' => (bool) $provider->is_active,
            'primary_branch_name' => $mainBranch?->name_ar ?? $mainBranch?->name_en,
            'primary_area_name' => $mainBranch?->area?->name_ar ?? $mainBranch?->area?->name_en,
            'primary_city_name' => $mainBranch?->city?->name_ar ?? $mainBranch?->city?->name_en,
        ];
    }

    private function staffPayload(ProviderStaff $staff): array
    {
        return [
            'id' => $staff->id,
            'provider_id' => $staff->provider_id,
            'user' => [
                'id' => $staff->user?->id,
                'name' => $staff->user?->name,
                'email' => $staff->user?->email,
            ],
            'role' => $staff->role->value,
            'is_owner' => (bool) $staff->is_owner,
            'status' => $staff->status,
            'permissions' => $staff->effectivePermissions(),
        ];
    }

    private function validatedAssignablePermissions(?array $permissions, ProviderStaff $actorStaff, ProviderStaffRole $role): array
    {
        if ($permissions === null) {
            return $role === ProviderStaffRole::Admin
                ? ProviderPermission::adminDefaults()
                : ProviderPermission::staffDefaults();
        }

        $valid = ProviderPermission::values();
        $clean = collect($permissions)
            ->filter(fn (mixed $permission): bool => is_string($permission))
            ->unique()
            ->values()
            ->all();

        if (array_diff($clean, $valid) !== []) {
            throw ValidationException::withMessages([
                'permissions' => ['Unsupported provider permission requested.'],
            ]);
        }

        if (! $actorStaff->is_owner && array_diff($clean, $actorStaff->effectivePermissions()) !== []) {
            throw ValidationException::withMessages([
                'permissions' => ['Staff cannot grant permissions they do not already have.'],
            ]);
        }

        return $clean;
    }

    private function todayCount(Provider $provider): int
    {
        return match ($provider->type) {
            ProviderType::Doctor => Appointment::query()->where('provider_id', $provider->id)->whereDate('booked_at', today())->count(),
            ProviderType::Hospital => Appointment::query()->where('hospital_provider_id', $provider->id)->whereDate('booked_at', today())->count(),
            ProviderType::Radiology => RadiologyOrder::query()->where('provider_id', $provider->id)->whereDate('created_at', today())->count(),
            ProviderType::Pharmacy => PharmacyOrder::query()->where('pharmacy_provider_id', $provider->id)->whereDate('created_at', today())->count(),
            ProviderType::Lab => LabOrder::query()->where('lab_provider_id', $provider->id)->whereDate('created_at', today())->count(),
            ProviderType::Gym => GymBooking::query()->where('provider_id', $provider->id)->whereDate('created_at', today())->count(),
            ProviderType::FitnessCoach, ProviderType::NutritionCoach => CoachBooking::query()->where('coach_provider_id', $provider->id)->whereDate('created_at', today())->count(),
            default => 0,
        };
    }

    private function pendingPaymentReviewCount(Provider $provider): int
    {
        return Payment::query()
            ->where('provider_id', $provider->id)
            ->where('status', PaymentStatus::PendingReview->value)
            ->count();
    }

    private function pendingActionsCount(Provider $provider): int
    {
        return match ($provider->type) {
            ProviderType::Doctor => Appointment::query()
                ->where('provider_id', $provider->id)
                ->whereIn('status', [AppointmentStatus::PendingPayment->value, AppointmentStatus::PendingPaymentReview->value])
                ->count(),
            ProviderType::Hospital => Appointment::query()
                ->where('hospital_provider_id', $provider->id)
                ->whereIn('status', [AppointmentStatus::PendingPayment->value, AppointmentStatus::PendingPaymentReview->value])
                ->count(),
            ProviderType::Radiology => RadiologyOrder::query()
                ->where('provider_id', $provider->id)
                ->whereIn('status', [RadiologyOrderStatus::Paid->value, RadiologyOrderStatus::Accepted->value, RadiologyOrderStatus::InProgress->value])
                ->count(),
            ProviderType::Pharmacy => PharmacyOrder::query()
                ->where('pharmacy_provider_id', $provider->id)
                ->where('order_status', PharmacyOrderStatus::Pending->value)
                ->count(),
            ProviderType::Lab => LabOrder::query()
                ->where('lab_provider_id', $provider->id)
                ->whereIn('order_status', [LabOrderStatus::LabReview->value, LabOrderStatus::Paid->value, LabOrderStatus::SampleCollected->value])
                ->count(),
            ProviderType::Gym => GymBooking::query()
                ->where('provider_id', $provider->id)
                ->whereIn('status', [GymBookingStatus::PendingPaymentReview->value, GymBookingStatus::Paid->value, GymBookingStatus::Confirmed->value])
                ->count(),
            ProviderType::FitnessCoach, ProviderType::NutritionCoach => CoachBooking::query()
                ->where('coach_provider_id', $provider->id)
                ->whereIn('status', [CoachBookingStatus::PendingPaymentReview->value, CoachBookingStatus::Paid->value, CoachBookingStatus::Confirmed->value])
                ->count(),
            default => 0,
        };
    }

    private function summaryCards(Provider $provider): array
    {
        return match ($provider->type) {
            ProviderType::Doctor => [
                $this->card('appointments_today', 'مواعيد اليوم', 'Today appointments', $this->todayCount($provider)),
                $this->card('upcoming_slots', 'مواعيد متاحة', 'Upcoming slots', $provider->doctorProfile?->slots()->where('status', AppointmentSlotStatus::Available->value)->count() ?? 0),
            ],
            ProviderType::Hospital => [
                $this->card('departments', 'الأقسام', 'Departments', HospitalDepartment::query()->where('hospital_provider_id', $provider->id)->where('is_active', true)->count()),
                $this->card('linked_doctors', 'الأطباء المرتبطون', 'Linked doctors', HospitalDoctor::query()->where('hospital_provider_id', $provider->id)->where('is_active', true)->count()),
            ],
            ProviderType::Radiology => [
                $this->card('pending_orders', 'طلبات قيد المتابعة', 'Pending orders', $this->pendingActionsCount($provider)),
                $this->card('scan_catalog', 'فحوصات مفعلة', 'Active scans', RadiologyScan::query()->where('provider_id', $provider->id)->where('is_active', true)->count()),
            ],
            ProviderType::Pharmacy => [
                $this->card('pending_orders', 'طلبات الصيدلية', 'Pharmacy orders', PharmacyOrder::query()->where('pharmacy_provider_id', $provider->id)->count()),
                $this->card('products', 'منتجات مفعلة', 'Active products', PharmacyProduct::query()->where('provider_id', $provider->id)->where('is_active', true)->count()),
            ],
            ProviderType::Lab => [
                $this->card('pending_orders', 'طلبات المعمل', 'Lab orders', LabOrder::query()->where('lab_provider_id', $provider->id)->count()),
                $this->card('catalog', 'تحاليل وباقات', 'Tests and packages', LabTest::query()->where('provider_id', $provider->id)->where('is_active', true)->count() + LabPackage::query()->where('provider_id', $provider->id)->where('is_active', true)->count()),
            ],
            ProviderType::Gym => [
                $this->card('active_bookings', 'حجوزات الجيم', 'Gym bookings', GymBooking::query()->where('provider_id', $provider->id)->count()),
                $this->card('plans_classes', 'خطط وحصص', 'Plans and classes', GymMembershipPlan::query()->where('provider_id', $provider->id)->where('is_active', true)->count() + GymClassModel::query()->where('provider_id', $provider->id)->where('is_active', true)->count()),
            ],
            ProviderType::FitnessCoach, ProviderType::NutritionCoach => [
                $this->card('sessions', 'جلسات قادمة', 'Upcoming sessions', CoachBooking::query()->where('coach_provider_id', $provider->id)->count()),
                $this->card('availability', 'مواعيد متاحة', 'Available slots', CoachAvailabilitySlot::query()->where('provider_id', $provider->id)->where('status', CoachAvailabilityStatus::Available->value)->count()),
                $this->card('packages', 'باقات', 'Packages', CoachPackage::query()->where('provider_id', $provider->id)->where('is_active', true)->count()),
                $this->card('session_types', 'أنواع الجلسات', 'Session types', CoachSessionType::query()->where('provider_id', $provider->id)->where('is_active', true)->count()),
            ],
            default => [
                $this->card('pending_actions', 'إجراءات مطلوبة', 'Pending actions', $this->pendingActionsCount($provider)),
            ],
        };
    }

    private function recentItems(Provider $provider): array
    {
        $items = match ($provider->type) {
            ProviderType::Doctor => Appointment::query()->where('provider_id', $provider->id)->latest()->limit(5)->get(['id', 'appointment_number', 'status', 'created_at']),
            ProviderType::Hospital => Appointment::query()->where('hospital_provider_id', $provider->id)->latest()->limit(5)->get(['id', 'appointment_number', 'status', 'created_at']),
            ProviderType::Radiology => RadiologyOrder::query()->where('provider_id', $provider->id)->latest()->limit(5)->get(['id', 'order_number', 'status', 'created_at']),
            ProviderType::Pharmacy => PharmacyOrder::query()->where('pharmacy_provider_id', $provider->id)->latest()->limit(5)->get(['id', 'order_number', 'order_status', 'created_at']),
            ProviderType::Lab => LabOrder::query()->where('lab_provider_id', $provider->id)->latest()->limit(5)->get(['id', 'order_number', 'order_status', 'created_at']),
            ProviderType::Gym => GymBooking::query()->where('provider_id', $provider->id)->latest()->limit(5)->get(['id', 'booking_number', 'status', 'created_at']),
            ProviderType::FitnessCoach, ProviderType::NutritionCoach => CoachBooking::query()->where('coach_provider_id', $provider->id)->latest()->limit(5)->get(['id', 'booking_number', 'status', 'created_at']),
            default => collect(),
        };

        return $items instanceof Collection
            ? $items->map(fn (mixed $item): array => [
                'id' => $item->id,
                'number' => $item->appointment_number ?? $item->order_number ?? $item->booking_number ?? null,
                'status' => $item->status?->value ?? $item->order_status?->value ?? (string) ($item->status ?? $item->order_status ?? ''),
                'created_at' => $item->created_at?->toISOString(),
            ])->all()
            : [];
    }

    private function quickActionsFor(ProviderType $type, array $permissions): array
    {
        $actions = match ($type) {
            ProviderType::Doctor => [
                ['key' => 'schedule', 'label_ar' => 'جدول المواعيد', 'label_en' => 'Schedule', 'permission' => ProviderPermission::ManageSchedules->value],
                ['key' => 'appointments', 'label_ar' => 'الحجوزات', 'label_en' => 'Appointments', 'permission' => ProviderPermission::ViewAppointments->value],
                ['key' => 'payments', 'label_ar' => 'المدفوعات', 'label_en' => 'Payments', 'permission' => ProviderPermission::ViewPayments->value],
            ],
            ProviderType::Hospital => [
                ['key' => 'departments', 'label_ar' => 'الأقسام', 'label_en' => 'Departments', 'permission' => ProviderPermission::ManageDepartments->value],
                ['key' => 'doctors', 'label_ar' => 'أطباء المستشفى', 'label_en' => 'Hospital doctors', 'permission' => ProviderPermission::ManageHospitalDoctors->value],
                ['key' => 'appointments', 'label_ar' => 'الحجوزات', 'label_en' => 'Appointments', 'permission' => ProviderPermission::ViewBookings->value],
                ['key' => 'reports', 'label_ar' => 'التقارير', 'label_en' => 'Reports', 'permission' => ProviderPermission::ViewHospitalReports->value],
            ],
            ProviderType::Radiology => [
                ['key' => 'orders', 'label_ar' => 'طلبات الأشعة', 'label_en' => 'Orders', 'permission' => ProviderPermission::ViewRadiologyOrders->value],
                ['key' => 'upload_result', 'label_ar' => 'رفع نتيجة', 'label_en' => 'Upload result', 'permission' => ProviderPermission::UploadRadiologyResults->value],
                ['key' => 'scan_catalog', 'label_ar' => 'فهرس الفحوصات', 'label_en' => 'Scan catalog', 'permission' => ProviderPermission::ManageRadiologyCatalog->value],
            ],
            ProviderType::Pharmacy => [
                ['key' => 'orders', 'label_ar' => 'طلبات الصيدلية', 'label_en' => 'Orders', 'permission' => ProviderPermission::ViewPharmacyOrders->value],
                ['key' => 'products', 'label_ar' => 'المنتجات', 'label_en' => 'Products', 'permission' => ProviderPermission::ManagePharmacyProducts->value],
                ['key' => 'prescriptions', 'label_ar' => 'الروشتات', 'label_en' => 'Prescriptions', 'permission' => ProviderPermission::ReviewPrescriptions->value],
            ],
            ProviderType::Lab => [
                ['key' => 'orders', 'label_ar' => 'طلبات المعمل', 'label_en' => 'Orders', 'permission' => ProviderPermission::ViewLabOrders->value],
                ['key' => 'upload_result', 'label_ar' => 'رفع نتيجة', 'label_en' => 'Upload result', 'permission' => ProviderPermission::UploadLabResults->value],
                ['key' => 'catalog', 'label_ar' => 'فهرس التحاليل', 'label_en' => 'Catalog', 'permission' => ProviderPermission::ManageLabCatalog->value],
            ],
            ProviderType::Gym => [
                ['key' => 'bookings', 'label_ar' => 'حجوزات الجيم', 'label_en' => 'Bookings', 'permission' => ProviderPermission::ViewGymBookings->value],
                ['key' => 'plans', 'label_ar' => 'الاشتراكات', 'label_en' => 'Plans', 'permission' => ProviderPermission::ManageGymPlans->value],
                ['key' => 'classes', 'label_ar' => 'الحصص', 'label_en' => 'Classes', 'permission' => ProviderPermission::ManageGymClasses->value],
            ],
            ProviderType::FitnessCoach, ProviderType::NutritionCoach => [
                ['key' => 'bookings', 'label_ar' => 'الحجوزات', 'label_en' => 'Bookings', 'permission' => ProviderPermission::ViewCoachBookings->value],
                ['key' => 'availability', 'label_ar' => 'المواعيد المتاحة', 'label_en' => 'Availability', 'permission' => ProviderPermission::ManageCoachAvailability->value],
                ['key' => 'session_types', 'label_ar' => 'أنواع الجلسات', 'label_en' => 'Session types', 'permission' => ProviderPermission::ManageCoachSessions->value],
            ],
            default => [
                ['key' => 'profile', 'label_ar' => 'الملف', 'label_en' => 'Profile', 'permission' => ProviderPermission::ManageProfile->value],
            ],
        };

        return collect($actions)
            ->filter(fn (array $action): bool => in_array($action['permission'], $permissions, true))
            ->map(fn (array $action): array => collect($action)->except('permission')->all())
            ->values()
            ->all();
    }

    private function card(string $key, string $labelAr, string $labelEn, int|float $value): array
    {
        return [
            'key' => $key,
            'label_ar' => $labelAr,
            'label_en' => $labelEn,
            'value' => $value,
        ];
    }
}
