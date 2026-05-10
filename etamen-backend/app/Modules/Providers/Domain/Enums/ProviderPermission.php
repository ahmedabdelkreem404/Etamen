<?php

namespace App\Modules\Providers\Domain\Enums;

enum ProviderPermission: string
{
    case ManageProfile = 'manage_profile';
    case ManageBranches = 'manage_branches';
    case ManageStaff = 'manage_staff';
    case ViewReports = 'view_reports';

    case ViewBookings = 'view_bookings';
    case ManageBookings = 'manage_bookings';
    case CreateBookings = 'create_bookings';

    case ViewPayments = 'view_payments';
    case ReviewPaymentProofs = 'review_payment_proofs';
    case ViewWallet = 'view_wallet';

    case ManageSchedules = 'manage_schedules';
    case ManageSlots = 'manage_slots';
    case ViewAppointments = 'view_appointments';
    case ManageAppointments = 'manage_appointments';

    case ManageDepartments = 'manage_departments';
    case ManageHospitalDoctors = 'manage_hospital_doctors';
    case ViewHospitalReports = 'view_hospital_reports';

    case ManageRadiologyCatalog = 'manage_radiology_catalog';
    case ViewRadiologyOrders = 'view_radiology_orders';
    case ManageRadiologyOrders = 'manage_radiology_orders';
    case UploadRadiologyResults = 'upload_radiology_results';

    case ManagePharmacyProducts = 'manage_pharmacy_products';
    case ViewPharmacyOrders = 'view_pharmacy_orders';
    case ManagePharmacyOrders = 'manage_pharmacy_orders';
    case ReviewPrescriptions = 'review_prescriptions';

    case ManageLabCatalog = 'manage_lab_catalog';
    case ViewLabOrders = 'view_lab_orders';
    case ManageLabOrders = 'manage_lab_orders';
    case UploadLabResults = 'upload_lab_results';

    case ManageGymPlans = 'manage_gym_plans';
    case ManageGymClasses = 'manage_gym_classes';
    case ViewGymBookings = 'view_gym_bookings';
    case ManageGymBookings = 'manage_gym_bookings';

    case ManageCoachSessions = 'manage_coach_sessions';
    case ManageCoachAvailability = 'manage_coach_availability';
    case ViewCoachBookings = 'view_coach_bookings';
    case ManageCoachBookings = 'manage_coach_bookings';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function ownerValues(): array
    {
        return self::values();
    }

    public static function adminDefaults(): array
    {
        return [
            self::ManageProfile->value,
            self::ManageBranches->value,
            self::ManageStaff->value,
            self::ViewReports->value,
            self::ViewBookings->value,
            self::ManageBookings->value,
            self::ViewPayments->value,
            self::ReviewPaymentProofs->value,
            self::ViewWallet->value,
        ];
    }

    public static function staffDefaults(): array
    {
        return [
            self::ViewBookings->value,
            self::ViewPayments->value,
        ];
    }
}
