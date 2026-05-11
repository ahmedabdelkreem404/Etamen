<?php

use App\Modules\Pharmacies\Http\Controllers\AdminPharmacyOrderController;
use App\Modules\Pharmacies\Http\Controllers\PatientPharmacyOrderController;
use App\Modules\Pharmacies\Http\Controllers\PatientPharmacyPrescriptionController;
use App\Modules\Pharmacies\Http\Controllers\ProviderPharmacyOrderController;
use App\Modules\Pharmacies\Http\Controllers\ProviderPharmacyProductController;
use App\Modules\Pharmacies\Http\Controllers\PublicPharmacyProductController;
use Illuminate\Support\Facades\Route;

Route::get('/pharmacies/{pharmacy}/products', [PublicPharmacyProductController::class, 'index']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/pharmacy/prescriptions', [PatientPharmacyPrescriptionController::class, 'store'])->middleware('throttle:file-upload');
    Route::get('/pharmacy/prescriptions/{prescription}/download', [PatientPharmacyPrescriptionController::class, 'download']);

    Route::post('/pharmacy/orders', [PatientPharmacyOrderController::class, 'store'])->middleware('throttle:sensitive-action');
    Route::get('/pharmacy/orders', [PatientPharmacyOrderController::class, 'index']);
    Route::get('/pharmacy/orders/{order}', [PatientPharmacyOrderController::class, 'show']);
    Route::post('/pharmacy/orders/{order}/pay', [PatientPharmacyOrderController::class, 'pay'])->middleware('throttle:sensitive-action');
    Route::post('/pharmacy/orders/{order}/cancel', [PatientPharmacyOrderController::class, 'cancel'])->middleware('throttle:sensitive-action');
});

Route::prefix('provider/pharmacy')->middleware(['auth:sanctum', 'provider.user'])->group(function (): void {
    Route::get('/products', [ProviderPharmacyProductController::class, 'index']);
    Route::post('/products', [ProviderPharmacyProductController::class, 'store'])->middleware('throttle:sensitive-action');
    Route::get('/products/{product}', [ProviderPharmacyProductController::class, 'show']);
    Route::patch('/products/{product}', [ProviderPharmacyProductController::class, 'update'])->middleware('throttle:sensitive-action');
    Route::delete('/products/{product}', [ProviderPharmacyProductController::class, 'destroy'])->middleware('throttle:sensitive-action');

    Route::get('/orders', [ProviderPharmacyOrderController::class, 'index']);
    Route::get('/orders/{order}', [ProviderPharmacyOrderController::class, 'show']);
    Route::patch('/orders/{order}/status', [ProviderPharmacyOrderController::class, 'updateStatus'])->middleware('throttle:sensitive-action');
});

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function (): void {
    Route::get('/pharmacy-orders', [AdminPharmacyOrderController::class, 'index']);
    Route::get('/pharmacy-orders/{order}', [AdminPharmacyOrderController::class, 'show']);
    Route::patch('/pharmacy-orders/{order}/status', [AdminPharmacyOrderController::class, 'updateStatus'])->middleware('throttle:admin-sensitive');
});
