<?php

use App\Modules\Radiology\Http\Controllers\AdminRadiologyPreparationInstructionController;
use App\Modules\Radiology\Http\Controllers\AdminRadiologyOrderController;
use App\Modules\Radiology\Http\Controllers\AdminRadiologyScanCategoryController;
use App\Modules\Radiology\Http\Controllers\AdminRadiologyScanController;
use App\Modules\Radiology\Http\Controllers\PatientRadiologyOrderController;
use App\Modules\Radiology\Http\Controllers\ProviderRadiologyOrderController;
use App\Modules\Radiology\Http\Controllers\ProviderRadiologyScanController;
use App\Modules\Radiology\Http\Controllers\PublicRadiologyCatalogController;
use App\Modules\Radiology\Http\Controllers\RadiologyResultDownloadController;
use Illuminate\Support\Facades\Route;

Route::get('/radiology/scan-categories', [PublicRadiologyCatalogController::class, 'categories']);
Route::get('/radiology/scans', [PublicRadiologyCatalogController::class, 'scans']);
Route::get('/radiology/preparation-instructions', [PublicRadiologyCatalogController::class, 'instructions']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/radiology/orders', [PatientRadiologyOrderController::class, 'index']);
    Route::post('/radiology/orders', [PatientRadiologyOrderController::class, 'store'])->middleware('throttle:sensitive-action');
    Route::get('/radiology/orders/{order}', [PatientRadiologyOrderController::class, 'show']);
    Route::post('/radiology/orders/{order}/cancel', [PatientRadiologyOrderController::class, 'cancel'])->middleware('throttle:sensitive-action');
    Route::get('/radiology/orders/{order}/results', [PatientRadiologyOrderController::class, 'results']);
    Route::get('/radiology/results/{result}/download', [RadiologyResultDownloadController::class, 'download']);
});

Route::prefix('provider/radiology')->middleware(['auth:sanctum', 'provider.user'])->group(function (): void {
    Route::get('/scans', [ProviderRadiologyScanController::class, 'index']);
    Route::post('/scans', [ProviderRadiologyScanController::class, 'store'])->middleware('throttle:sensitive-action');
    Route::get('/scans/{scan}', [ProviderRadiologyScanController::class, 'show']);
    Route::patch('/scans/{scan}', [ProviderRadiologyScanController::class, 'update'])->middleware('throttle:sensitive-action');
    Route::delete('/scans/{scan}', [ProviderRadiologyScanController::class, 'destroy'])->middleware('throttle:sensitive-action');

    Route::get('/orders', [ProviderRadiologyOrderController::class, 'index']);
    Route::get('/orders/{order}', [ProviderRadiologyOrderController::class, 'show']);
    Route::post('/orders/{order}/accept', [ProviderRadiologyOrderController::class, 'accept'])->middleware('throttle:sensitive-action');
    Route::post('/orders/{order}/reject', [ProviderRadiologyOrderController::class, 'reject'])->middleware('throttle:sensitive-action');
    Route::post('/orders/{order}/start', [ProviderRadiologyOrderController::class, 'start'])->middleware('throttle:sensitive-action');
    Route::post('/orders/{order}/mark-result-ready', [ProviderRadiologyOrderController::class, 'markResultReady'])->middleware('throttle:sensitive-action');
    Route::post('/orders/{order}/complete', [ProviderRadiologyOrderController::class, 'complete'])->middleware('throttle:sensitive-action');
    Route::post('/orders/{order}/results', [ProviderRadiologyOrderController::class, 'uploadResult'])->middleware('throttle:file-upload');
});

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function (): void {
    Route::get('/radiology-orders', [AdminRadiologyOrderController::class, 'index']);
    Route::get('/radiology-orders/{order}', [AdminRadiologyOrderController::class, 'show']);
    Route::post('/radiology-orders/{order}/force-cancel', [AdminRadiologyOrderController::class, 'forceCancel'])->middleware('throttle:admin-sensitive');
    Route::get('/radiology-orders/{order}/status-history', [AdminRadiologyOrderController::class, 'statusHistory']);
    Route::post('/radiology-orders/{order}/results', [AdminRadiologyOrderController::class, 'uploadResult'])->middleware('throttle:file-upload');

    Route::get('/radiology-scan-categories', [AdminRadiologyScanCategoryController::class, 'index']);
    Route::post('/radiology-scan-categories', [AdminRadiologyScanCategoryController::class, 'store'])->middleware('throttle:admin-sensitive');
    Route::patch('/radiology-scan-categories/{category}', [AdminRadiologyScanCategoryController::class, 'update'])->middleware('throttle:admin-sensitive');
    Route::delete('/radiology-scan-categories/{category}', [AdminRadiologyScanCategoryController::class, 'destroy'])->middleware('throttle:admin-sensitive');

    Route::get('/radiology-scans', [AdminRadiologyScanController::class, 'index']);
    Route::post('/radiology-scans', [AdminRadiologyScanController::class, 'store'])->middleware('throttle:admin-sensitive');
    Route::patch('/radiology-scans/{scan}', [AdminRadiologyScanController::class, 'update'])->middleware('throttle:admin-sensitive');
    Route::delete('/radiology-scans/{scan}', [AdminRadiologyScanController::class, 'destroy'])->middleware('throttle:admin-sensitive');

    Route::get('/radiology-preparation-instructions', [AdminRadiologyPreparationInstructionController::class, 'index']);
    Route::post('/radiology-preparation-instructions', [AdminRadiologyPreparationInstructionController::class, 'store'])->middleware('throttle:admin-sensitive');
    Route::patch('/radiology-preparation-instructions/{instruction}', [AdminRadiologyPreparationInstructionController::class, 'update'])->middleware('throttle:admin-sensitive');
    Route::delete('/radiology-preparation-instructions/{instruction}', [AdminRadiologyPreparationInstructionController::class, 'destroy'])->middleware('throttle:admin-sensitive');
});
