<?php

use App\Modules\Labs\Http\Controllers\AdminLabOrderController;
use App\Modules\Labs\Http\Controllers\AdminLabResultController;
use App\Modules\Labs\Http\Controllers\LabResultDownloadController;
use App\Modules\Labs\Http\Controllers\PatientLabOrderController;
use App\Modules\Labs\Http\Controllers\ProviderLabOrderController;
use App\Modules\Labs\Http\Controllers\ProviderLabPackageController;
use App\Modules\Labs\Http\Controllers\ProviderLabTestController;
use App\Modules\Labs\Http\Controllers\PublicLabCatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/labs/{lab}/tests', [PublicLabCatalogController::class, 'tests']);
Route::get('/labs/{lab}/packages', [PublicLabCatalogController::class, 'packages']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/lab/orders', [PatientLabOrderController::class, 'store'])->middleware('throttle:sensitive-action');
    Route::get('/lab/orders', [PatientLabOrderController::class, 'index']);
    Route::get('/lab/orders/{order}', [PatientLabOrderController::class, 'show']);
    Route::post('/lab/orders/{order}/pay', [PatientLabOrderController::class, 'pay'])->middleware('throttle:sensitive-action');
    Route::get('/lab/orders/{order}/results', [PatientLabOrderController::class, 'results']);
    Route::get('/lab/results/{result}/download', [LabResultDownloadController::class, 'download']);
});

Route::prefix('provider/lab')->middleware(['auth:sanctum', 'provider.user'])->group(function (): void {
    Route::get('/tests', [ProviderLabTestController::class, 'index']);
    Route::post('/tests', [ProviderLabTestController::class, 'store'])->middleware('throttle:sensitive-action');
    Route::get('/tests/{test}', [ProviderLabTestController::class, 'show']);
    Route::patch('/tests/{test}', [ProviderLabTestController::class, 'update'])->middleware('throttle:sensitive-action');
    Route::delete('/tests/{test}', [ProviderLabTestController::class, 'destroy'])->middleware('throttle:sensitive-action');

    Route::get('/packages', [ProviderLabPackageController::class, 'index']);
    Route::post('/packages', [ProviderLabPackageController::class, 'store'])->middleware('throttle:sensitive-action');
    Route::get('/packages/{package}', [ProviderLabPackageController::class, 'show']);
    Route::patch('/packages/{package}', [ProviderLabPackageController::class, 'update'])->middleware('throttle:sensitive-action');
    Route::delete('/packages/{package}', [ProviderLabPackageController::class, 'destroy'])->middleware('throttle:sensitive-action');

    Route::get('/orders', [ProviderLabOrderController::class, 'index']);
    Route::get('/orders/{order}', [ProviderLabOrderController::class, 'show']);
    Route::patch('/orders/{order}/status', [ProviderLabOrderController::class, 'updateStatus'])->middleware('throttle:sensitive-action');
    Route::post('/orders/{order}/results', [ProviderLabOrderController::class, 'uploadResult'])->middleware('throttle:file-upload');
});

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function (): void {
    Route::get('/lab-orders', [AdminLabOrderController::class, 'index']);
    Route::get('/lab-orders/{order}', [AdminLabOrderController::class, 'show']);
    Route::patch('/lab-orders/{order}/status', [AdminLabOrderController::class, 'updateStatus'])->middleware('throttle:admin-sensitive');
    Route::get('/lab-results', [AdminLabResultController::class, 'index']);
    Route::get('/lab-results/{result}', [AdminLabResultController::class, 'show']);
});
