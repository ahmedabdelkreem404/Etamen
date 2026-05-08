<?php

use App\Modules\Radiology\Http\Controllers\AdminRadiologyPreparationInstructionController;
use App\Modules\Radiology\Http\Controllers\AdminRadiologyScanCategoryController;
use App\Modules\Radiology\Http\Controllers\AdminRadiologyScanController;
use App\Modules\Radiology\Http\Controllers\ProviderRadiologyScanController;
use App\Modules\Radiology\Http\Controllers\PublicRadiologyCatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/radiology/scan-categories', [PublicRadiologyCatalogController::class, 'categories']);
Route::get('/radiology/scans', [PublicRadiologyCatalogController::class, 'scans']);
Route::get('/radiology/preparation-instructions', [PublicRadiologyCatalogController::class, 'instructions']);

Route::prefix('provider/radiology')->middleware(['auth:sanctum', 'provider.user'])->group(function (): void {
    Route::get('/scans', [ProviderRadiologyScanController::class, 'index']);
    Route::post('/scans', [ProviderRadiologyScanController::class, 'store'])->middleware('throttle:sensitive-action');
    Route::get('/scans/{scan}', [ProviderRadiologyScanController::class, 'show']);
    Route::patch('/scans/{scan}', [ProviderRadiologyScanController::class, 'update'])->middleware('throttle:sensitive-action');
    Route::delete('/scans/{scan}', [ProviderRadiologyScanController::class, 'destroy'])->middleware('throttle:sensitive-action');
});

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function (): void {
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
