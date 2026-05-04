<?php

use App\Modules\Providers\Http\Controllers\AdminProviderController;
use App\Modules\Providers\Http\Controllers\AdminSpecialtyController;
use App\Modules\Providers\Http\Controllers\ProviderAccountController;
use App\Modules\Providers\Http\Controllers\ProviderRegistrationController;
use App\Modules\Providers\Http\Controllers\PublicProviderController;
use Illuminate\Support\Facades\Route;

Route::prefix('providers')->middleware('throttle:10,1')->group(function (): void {
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
Route::get('/specialties', [PublicProviderController::class, 'specialties']);

Route::prefix('provider')->middleware(['auth:sanctum', 'provider.user'])->group(function (): void {
    Route::get('/me', [ProviderAccountController::class, 'me']);
    Route::put('/profile', [ProviderAccountController::class, 'updateProfile']);
    Route::get('/branches', [ProviderAccountController::class, 'branches']);
    Route::post('/branches', [ProviderAccountController::class, 'createBranch']);
    Route::put('/branches/{branch}', [ProviderAccountController::class, 'updateBranch']);
    Route::post('/documents', [ProviderAccountController::class, 'uploadDocument']);
});

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function (): void {
    Route::get('/providers', [AdminProviderController::class, 'index']);
    Route::get('/providers/pending-approvals', [AdminProviderController::class, 'pendingApprovals']);
    Route::post('/providers/{provider}/approve', [AdminProviderController::class, 'approve']);
    Route::post('/providers/{provider}/reject', [AdminProviderController::class, 'reject']);
    Route::post('/providers/{provider}/suspend', [AdminProviderController::class, 'suspend']);
    Route::post('/providers/{provider}/reactivate', [AdminProviderController::class, 'reactivate']);

    Route::post('/specialties', [AdminSpecialtyController::class, 'store']);
    Route::put('/specialties/{specialty}', [AdminSpecialtyController::class, 'update']);
    Route::delete('/specialties/{specialty}', [AdminSpecialtyController::class, 'destroy']);
});
