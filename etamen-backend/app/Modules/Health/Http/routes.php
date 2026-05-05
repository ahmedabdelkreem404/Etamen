<?php

use App\Modules\Health\Http\Controllers\AdminHealthController;
use App\Modules\Health\Http\Controllers\AllergyController;
use App\Modules\Health\Http\Controllers\ChronicDiseaseController;
use App\Modules\Health\Http\Controllers\CurrentMedicationController;
use App\Modules\Health\Http\Controllers\HealthGoalController;
use App\Modules\Health\Http\Controllers\HealthProfileController;
use App\Modules\Health\Http\Controllers\SurgeryController;
use App\Modules\Health\Http\Controllers\VitalRecordController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'patient'])->prefix('health')->group(function (): void {
    Route::get('/profile', [HealthProfileController::class, 'show']);
    Route::put('/profile', [HealthProfileController::class, 'update']);

    Route::get('/chronic-diseases', [ChronicDiseaseController::class, 'index']);
    Route::post('/chronic-diseases', [ChronicDiseaseController::class, 'store']);
    Route::put('/chronic-diseases/{disease}', [ChronicDiseaseController::class, 'update']);
    Route::delete('/chronic-diseases/{disease}', [ChronicDiseaseController::class, 'destroy']);

    Route::get('/allergies', [AllergyController::class, 'index']);
    Route::post('/allergies', [AllergyController::class, 'store']);
    Route::put('/allergies/{allergy}', [AllergyController::class, 'update']);
    Route::delete('/allergies/{allergy}', [AllergyController::class, 'destroy']);

    Route::get('/current-medications', [CurrentMedicationController::class, 'index']);
    Route::post('/current-medications', [CurrentMedicationController::class, 'store']);
    Route::put('/current-medications/{medication}', [CurrentMedicationController::class, 'update']);
    Route::delete('/current-medications/{medication}', [CurrentMedicationController::class, 'destroy']);

    Route::get('/surgeries', [SurgeryController::class, 'index']);
    Route::post('/surgeries', [SurgeryController::class, 'store']);
    Route::put('/surgeries/{surgery}', [SurgeryController::class, 'update']);
    Route::delete('/surgeries/{surgery}', [SurgeryController::class, 'destroy']);

    Route::get('/goals', [HealthGoalController::class, 'index']);
    Route::post('/goals', [HealthGoalController::class, 'store']);
    Route::put('/goals/{goal}', [HealthGoalController::class, 'update']);
    Route::delete('/goals/{goal}', [HealthGoalController::class, 'destroy']);

    Route::get('/vitals/trends', [VitalRecordController::class, 'trends']);
    Route::get('/vitals/latest', [VitalRecordController::class, 'latest']);
    Route::get('/summary', [VitalRecordController::class, 'summary']);
    Route::get('/vitals', [VitalRecordController::class, 'index']);
    Route::post('/vitals', [VitalRecordController::class, 'store']);
    Route::get('/vitals/{vital}', [VitalRecordController::class, 'show']);
    Route::put('/vitals/{vital}', [VitalRecordController::class, 'update']);
    Route::delete('/vitals/{vital}', [VitalRecordController::class, 'destroy']);
});

Route::prefix('admin/health')->middleware(['auth:sanctum', 'admin'])->group(function (): void {
    Route::get('/profiles', [AdminHealthController::class, 'profiles']);
    Route::get('/profiles/{profile}', [AdminHealthController::class, 'profile']);
    Route::get('/vitals', [AdminHealthController::class, 'vitals']);
    Route::get('/vitals/{vital}', [AdminHealthController::class, 'vital']);
    Route::get('/access-logs', [AdminHealthController::class, 'accessLogs']);
});
