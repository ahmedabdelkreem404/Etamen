<?php

use App\Modules\CarePlans\Http\Controllers\AdminCarePlanController;
use App\Modules\CarePlans\Http\Controllers\CarePlanCheckinController;
use App\Modules\CarePlans\Http\Controllers\CarePlanDayController;
use App\Modules\CarePlans\Http\Controllers\CarePlanFoodItemController;
use App\Modules\CarePlans\Http\Controllers\CarePlanInstructionController;
use App\Modules\CarePlans\Http\Controllers\CarePlanMealController;
use App\Modules\CarePlans\Http\Controllers\CarePlanProgressController;
use App\Modules\CarePlans\Http\Controllers\CarePlanStatusController;
use App\Modules\CarePlans\Http\Controllers\MealLogController;
use App\Modules\CarePlans\Http\Controllers\PatientCarePlanController;
use App\Modules\CarePlans\Http\Controllers\ProviderCarePlanController;
use Illuminate\Support\Facades\Route;

Route::prefix('care-plans')->middleware(['auth:sanctum', 'patient'])->group(function (): void {
    Route::get('/summary', [CarePlanProgressController::class, 'summary']);
    Route::get('/', [PatientCarePlanController::class, 'index']);
    Route::post('/', [PatientCarePlanController::class, 'store']);
    Route::get('/{plan}', [PatientCarePlanController::class, 'show']);
    Route::put('/{plan}', [PatientCarePlanController::class, 'update']);
    Route::delete('/{plan}', [PatientCarePlanController::class, 'destroy']);

    Route::post('/{plan}/activate', [CarePlanStatusController::class, 'activate']);
    Route::post('/{plan}/pause', [CarePlanStatusController::class, 'pause']);
    Route::post('/{plan}/resume', [CarePlanStatusController::class, 'resume']);
    Route::post('/{plan}/complete', [CarePlanStatusController::class, 'complete']);
    Route::post('/{plan}/cancel', [CarePlanStatusController::class, 'cancel']);

    Route::get('/{plan}/days', [CarePlanDayController::class, 'index']);
    Route::post('/{plan}/days', [CarePlanDayController::class, 'store']);
    Route::put('/{plan}/days/{day}', [CarePlanDayController::class, 'update']);

    Route::get('/{plan}/meals', [CarePlanMealController::class, 'index']);
    Route::post('/{plan}/meals', [CarePlanMealController::class, 'store']);
    Route::put('/{plan}/meals/{meal}', [CarePlanMealController::class, 'update']);
    Route::delete('/{plan}/meals/{meal}', [CarePlanMealController::class, 'destroy']);

    Route::get('/{plan}/foods', [CarePlanFoodItemController::class, 'index']);
    Route::post('/{plan}/foods', [CarePlanFoodItemController::class, 'store']);
    Route::put('/{plan}/foods/{food}', [CarePlanFoodItemController::class, 'update']);
    Route::delete('/{plan}/foods/{food}', [CarePlanFoodItemController::class, 'destroy']);

    Route::get('/{plan}/instructions', [CarePlanInstructionController::class, 'index']);
    Route::post('/{plan}/instructions', [CarePlanInstructionController::class, 'store']);
    Route::put('/{plan}/instructions/{instruction}', [CarePlanInstructionController::class, 'update']);
    Route::delete('/{plan}/instructions/{instruction}', [CarePlanInstructionController::class, 'destroy']);

    Route::get('/{plan}/checkins', [CarePlanCheckinController::class, 'index']);
    Route::post('/{plan}/checkins', [CarePlanCheckinController::class, 'store']);
    Route::put('/{plan}/checkins/{checkin}', [CarePlanCheckinController::class, 'update']);

    Route::get('/{plan}/meal-logs', [MealLogController::class, 'index']);
    Route::post('/{plan}/meal-logs', [MealLogController::class, 'store']);
    Route::get('/{plan}/meal-logs/{log}', [MealLogController::class, 'show']);
    Route::put('/{plan}/meal-logs/{log}', [MealLogController::class, 'update']);
    Route::delete('/{plan}/meal-logs/{log}', [MealLogController::class, 'destroy']);

    Route::get('/{plan}/progress', [CarePlanProgressController::class, 'progress']);
});

Route::prefix('provider/care-plans')->middleware(['auth:sanctum', 'provider.user'])->group(function (): void {
    Route::get('/', [ProviderCarePlanController::class, 'index']);
    Route::post('/assign', [ProviderCarePlanController::class, 'assign']);
    Route::get('/{plan}', [ProviderCarePlanController::class, 'show']);
    Route::put('/{plan}', [ProviderCarePlanController::class, 'update']);
    Route::post('/{plan}/activate', [CarePlanStatusController::class, 'activate']);
    Route::post('/{plan}/pause', [CarePlanStatusController::class, 'pause']);
    Route::post('/{plan}/complete', [CarePlanStatusController::class, 'complete']);
    Route::post('/{plan}/cancel', [CarePlanStatusController::class, 'cancel']);
});

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function (): void {
    Route::get('/care-plans', [AdminCarePlanController::class, 'index']);
    Route::post('/care-plans', [AdminCarePlanController::class, 'store']);
    Route::get('/care-plans/{plan}', [AdminCarePlanController::class, 'show']);
    Route::put('/care-plans/{plan}', [AdminCarePlanController::class, 'update']);
    Route::post('/care-plans/{plan}/activate', [CarePlanStatusController::class, 'activate']);
    Route::post('/care-plans/{plan}/pause', [CarePlanStatusController::class, 'pause']);
    Route::post('/care-plans/{plan}/complete', [CarePlanStatusController::class, 'complete']);
    Route::post('/care-plans/{plan}/cancel', [CarePlanStatusController::class, 'cancel']);
    Route::get('/care-plan-checkins', [AdminCarePlanController::class, 'checkins']);
    Route::get('/meal-logs', [AdminCarePlanController::class, 'mealLogs']);
});
