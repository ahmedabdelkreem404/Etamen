<?php

use App\Modules\Providers\Http\Controllers\AdminProviderController;
use App\Modules\Providers\Http\Controllers\AdminSpecialtyController;
use App\Modules\Providers\Http\Controllers\MeWorkspaceController;
use App\Modules\Providers\Http\Controllers\ProviderAccountController;
use App\Modules\Providers\Http\Controllers\ProviderRegistrationController;
use App\Modules\Providers\Http\Controllers\ProviderServiceController;
use App\Modules\Providers\Http\Controllers\ProviderWorkspaceDashboardController;
use App\Modules\Providers\Http\Controllers\ProviderWorkspaceOperationsController;
use App\Modules\Providers\Http\Controllers\ProviderWorkspaceStaffController;
use App\Modules\Providers\Http\Controllers\PublicHospitalController;
use App\Modules\Providers\Http\Controllers\PublicProviderController;
use Illuminate\Support\Facades\Route;

Route::prefix('providers')->middleware('throttle:10,1')->group(function (): void {
    Route::post('/register', [ProviderRegistrationController::class, 'provider']);
    Route::post('/register-doctor', [ProviderRegistrationController::class, 'doctor']);
    Route::post('/register-pharmacy', [ProviderRegistrationController::class, 'pharmacy']);
    Route::post('/register-lab', [ProviderRegistrationController::class, 'lab']);
});

Route::get('/doctors', [PublicProviderController::class, 'doctors']);
Route::get('/doctors/{doctor}', [PublicProviderController::class, 'doctor']);
Route::get('/pharmacies', [PublicProviderController::class, 'pharmacies']);
Route::get('/pharmacies/{pharmacy}', [PublicProviderController::class, 'pharmacy']);
Route::get('/labs', [PublicProviderController::class, 'labs']);
Route::get('/labs/{lab}', [PublicProviderController::class, 'lab']);
Route::get('/hospitals', [PublicHospitalController::class, 'index']);
Route::get('/hospitals/{hospital}', [PublicHospitalController::class, 'show']);
Route::get('/hospitals/{hospital}/departments', [PublicHospitalController::class, 'departments']);
Route::get('/hospitals/{hospital}/doctors', [PublicHospitalController::class, 'doctors']);
Route::get('/hospitals/{hospital}/departments/{department}/doctors', [PublicHospitalController::class, 'departmentDoctors']);
Route::get('/specialties', [PublicProviderController::class, 'specialties']);

Route::get('/me/workspaces', [MeWorkspaceController::class, 'index'])
    ->middleware(['auth:sanctum']);

Route::prefix('provider')->middleware(['auth:sanctum', 'provider.user'])->group(function (): void {
    Route::get('/me', [ProviderAccountController::class, 'me']);
    Route::put('/profile', [ProviderAccountController::class, 'updateProfile']);
    Route::get('/branches', [ProviderAccountController::class, 'branches']);
    Route::post('/branches', [ProviderAccountController::class, 'createBranch'])->middleware('throttle:sensitive-action');
    Route::put('/branches/{branch}', [ProviderAccountController::class, 'updateBranch'])->middleware('throttle:sensitive-action');
    Route::get('/documents', [ProviderAccountController::class, 'documents']);
    Route::post('/documents', [ProviderAccountController::class, 'uploadDocument'])->middleware('throttle:file-upload');
    Route::get('/services', [ProviderServiceController::class, 'index']);
    Route::post('/services', [ProviderServiceController::class, 'store'])->middleware('throttle:sensitive-action');
    Route::put('/services/{service}', [ProviderServiceController::class, 'update'])->middleware('throttle:sensitive-action');
});

Route::prefix('provider/workspace/{provider}')->middleware(['auth:sanctum'])->group(function (): void {
    Route::get('/dashboard', [ProviderWorkspaceDashboardController::class, 'show']);
    Route::get('/staff', [ProviderWorkspaceStaffController::class, 'index']);
    Route::post('/staff', [ProviderWorkspaceStaffController::class, 'store'])->middleware('throttle:sensitive-action');
    Route::patch('/staff/{staff}', [ProviderWorkspaceStaffController::class, 'update'])->middleware('throttle:sensitive-action');
    Route::delete('/staff/{staff}', [ProviderWorkspaceStaffController::class, 'destroy'])->middleware('throttle:sensitive-action');

    Route::get('/doctor/appointments', [ProviderWorkspaceOperationsController::class, 'doctorAppointments']);
    Route::get('/doctor/appointments/{appointment}', [ProviderWorkspaceOperationsController::class, 'doctorAppointment']);
    Route::post('/doctor/appointments/{appointment}/confirm', [ProviderWorkspaceOperationsController::class, 'confirmDoctorAppointment'])->middleware('throttle:sensitive-action');
    Route::post('/doctor/appointments/{appointment}/complete', [ProviderWorkspaceOperationsController::class, 'completeDoctorAppointment'])->middleware('throttle:sensitive-action');
    Route::post('/doctor/appointments/{appointment}/cancel', [ProviderWorkspaceOperationsController::class, 'cancelDoctorAppointment'])->middleware('throttle:sensitive-action');

    Route::get('/hospital/appointments', [ProviderWorkspaceOperationsController::class, 'hospitalAppointments']);
    Route::get('/hospital/departments', [ProviderWorkspaceOperationsController::class, 'hospitalDepartments']);
    Route::get('/hospital/doctors', [ProviderWorkspaceOperationsController::class, 'hospitalDoctors']);

    Route::get('/radiology/orders', [ProviderWorkspaceOperationsController::class, 'radiologyOrders']);
    Route::get('/radiology/orders/{order}', [ProviderWorkspaceOperationsController::class, 'radiologyOrder']);
    Route::post('/radiology/orders/{order}/accept', [ProviderWorkspaceOperationsController::class, 'acceptRadiologyOrder'])->middleware('throttle:sensitive-action');
    Route::post('/radiology/orders/{order}/reject', [ProviderWorkspaceOperationsController::class, 'rejectRadiologyOrder'])->middleware('throttle:sensitive-action');
    Route::post('/radiology/orders/{order}/start', [ProviderWorkspaceOperationsController::class, 'startRadiologyOrder'])->middleware('throttle:sensitive-action');
    Route::post('/radiology/orders/{order}/result-ready', [ProviderWorkspaceOperationsController::class, 'markRadiologyResultReady'])->middleware('throttle:sensitive-action');
    Route::post('/radiology/orders/{order}/complete', [ProviderWorkspaceOperationsController::class, 'completeRadiologyOrder'])->middleware('throttle:sensitive-action');

    Route::get('/pharmacy/orders', [ProviderWorkspaceOperationsController::class, 'pharmacyOrders']);
    Route::get('/pharmacy/orders/{order}', [ProviderWorkspaceOperationsController::class, 'pharmacyOrder']);
    Route::post('/pharmacy/orders/{order}/accept', [ProviderWorkspaceOperationsController::class, 'acceptPharmacyOrder'])->middleware('throttle:sensitive-action');
    Route::post('/pharmacy/orders/{order}/reject', [ProviderWorkspaceOperationsController::class, 'rejectPharmacyOrder'])->middleware('throttle:sensitive-action');
    Route::post('/pharmacy/orders/{order}/preparing', [ProviderWorkspaceOperationsController::class, 'markPharmacyPreparing'])->middleware('throttle:sensitive-action');
    Route::post('/pharmacy/orders/{order}/ready', [ProviderWorkspaceOperationsController::class, 'markPharmacyReady'])->middleware('throttle:sensitive-action');
    Route::post('/pharmacy/orders/{order}/out-for-delivery', [ProviderWorkspaceOperationsController::class, 'markPharmacyOutForDelivery'])->middleware('throttle:sensitive-action');
    Route::post('/pharmacy/orders/{order}/complete', [ProviderWorkspaceOperationsController::class, 'completePharmacyOrder'])->middleware('throttle:sensitive-action');
    Route::get('/pharmacy/products', [ProviderWorkspaceOperationsController::class, 'pharmacyProducts']);

    Route::get('/lab/orders', [ProviderWorkspaceOperationsController::class, 'labOrders']);
    Route::get('/lab/orders/{order}', [ProviderWorkspaceOperationsController::class, 'labOrder']);
    Route::post('/lab/orders/{order}/accept', [ProviderWorkspaceOperationsController::class, 'acceptLabOrder'])->middleware('throttle:sensitive-action');
    Route::post('/lab/orders/{order}/reject', [ProviderWorkspaceOperationsController::class, 'rejectLabOrder'])->middleware('throttle:sensitive-action');
    Route::post('/lab/orders/{order}/sample-scheduled', [ProviderWorkspaceOperationsController::class, 'scheduleLabSample'])->middleware('throttle:sensitive-action');
    Route::post('/lab/orders/{order}/sample-collected', [ProviderWorkspaceOperationsController::class, 'markLabSampleCollected'])->middleware('throttle:sensitive-action');
    Route::post('/lab/orders/{order}/processing', [ProviderWorkspaceOperationsController::class, 'markLabProcessing'])->middleware('throttle:sensitive-action');
    Route::post('/lab/orders/{order}/result-ready', [ProviderWorkspaceOperationsController::class, 'markLabResultReady'])->middleware('throttle:sensitive-action');
    Route::post('/lab/orders/{order}/complete', [ProviderWorkspaceOperationsController::class, 'completeLabOrder'])->middleware('throttle:sensitive-action');
    Route::get('/lab/catalog', [ProviderWorkspaceOperationsController::class, 'labCatalog']);

    Route::get('/gym/bookings', [ProviderWorkspaceOperationsController::class, 'gymBookings']);
    Route::get('/gym/bookings/{booking}', [ProviderWorkspaceOperationsController::class, 'gymBooking']);
    Route::post('/gym/bookings/{booking}/confirm', [ProviderWorkspaceOperationsController::class, 'confirmGymBooking'])->middleware('throttle:sensitive-action');
    Route::post('/gym/bookings/{booking}/activate', [ProviderWorkspaceOperationsController::class, 'activateGymBooking'])->middleware('throttle:sensitive-action');
    Route::post('/gym/bookings/{booking}/complete', [ProviderWorkspaceOperationsController::class, 'completeGymBooking'])->middleware('throttle:sensitive-action');
    Route::post('/gym/bookings/{booking}/cancel', [ProviderWorkspaceOperationsController::class, 'cancelGymBooking'])->middleware('throttle:sensitive-action');
    Route::get('/gym/plans', [ProviderWorkspaceOperationsController::class, 'gymPlans']);
    Route::get('/gym/classes', [ProviderWorkspaceOperationsController::class, 'gymClasses']);

    Route::get('/coach/bookings', [ProviderWorkspaceOperationsController::class, 'coachBookings']);
    Route::get('/coach/bookings/{booking}', [ProviderWorkspaceOperationsController::class, 'coachBooking']);
    Route::post('/coach/bookings/{booking}/confirm', [ProviderWorkspaceOperationsController::class, 'confirmCoachBooking'])->middleware('throttle:sensitive-action');
    Route::post('/coach/bookings/{booking}/start', [ProviderWorkspaceOperationsController::class, 'startCoachBooking'])->middleware('throttle:sensitive-action');
    Route::post('/coach/bookings/{booking}/complete', [ProviderWorkspaceOperationsController::class, 'completeCoachBooking'])->middleware('throttle:sensitive-action');
    Route::post('/coach/bookings/{booking}/cancel', [ProviderWorkspaceOperationsController::class, 'cancelCoachBooking'])->middleware('throttle:sensitive-action');
    Route::get('/coach/availability', [ProviderWorkspaceOperationsController::class, 'coachAvailability']);
    Route::get('/coach/session-types', [ProviderWorkspaceOperationsController::class, 'coachSessionTypes']);
    Route::get('/coach/packages', [ProviderWorkspaceOperationsController::class, 'coachPackages']);
});

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function (): void {
    Route::get('/providers', [AdminProviderController::class, 'index']);
    Route::get('/providers/pending-approvals', [AdminProviderController::class, 'pendingApprovals']);
    Route::post('/providers/{provider}/approve', [AdminProviderController::class, 'approve'])->middleware('throttle:admin-sensitive');
    Route::post('/providers/{provider}/reject', [AdminProviderController::class, 'reject'])->middleware('throttle:admin-sensitive');
    Route::post('/providers/{provider}/request-changes', [AdminProviderController::class, 'requestChanges'])->middleware('throttle:admin-sensitive');
    Route::post('/providers/{provider}/suspend', [AdminProviderController::class, 'suspend'])->middleware('throttle:admin-sensitive');
    Route::post('/providers/{provider}/reactivate', [AdminProviderController::class, 'reactivate'])->middleware('throttle:admin-sensitive');
    Route::post('/providers/{provider}/contracts', [AdminProviderController::class, 'storeContract'])->middleware('throttle:admin-sensitive');
    Route::post('/provider-documents/{document}/approve', [AdminProviderController::class, 'approveDocument'])->middleware('throttle:admin-sensitive');
    Route::post('/provider-documents/{document}/reject', [AdminProviderController::class, 'rejectDocument'])->middleware('throttle:admin-sensitive');

    Route::post('/specialties', [AdminSpecialtyController::class, 'store']);
    Route::put('/specialties/{specialty}', [AdminSpecialtyController::class, 'update']);
    Route::delete('/specialties/{specialty}', [AdminSpecialtyController::class, 'destroy']);
});
