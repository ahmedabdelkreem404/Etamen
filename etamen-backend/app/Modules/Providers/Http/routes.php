<?php

use App\Modules\Providers\Http\Controllers\AdminProviderController;
use App\Modules\Providers\Http\Controllers\AdminSpecialtyController;
use App\Modules\Providers\Http\Controllers\MeWorkspaceController;
use App\Modules\Providers\Http\Controllers\ProviderAccountController;
use App\Modules\Providers\Http\Controllers\ProviderRegistrationController;
use App\Modules\Providers\Http\Controllers\ProviderServiceController;
use App\Modules\Providers\Http\Controllers\ProviderWorkspaceDashboardController;
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
