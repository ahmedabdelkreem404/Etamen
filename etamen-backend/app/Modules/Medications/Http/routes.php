<?php

use App\Modules\Medications\Http\Controllers\AdminMedicationController;
use App\Modules\Medications\Http\Controllers\MedicationLogController;
use App\Modules\Medications\Http\Controllers\MedicationRefillController;
use App\Modules\Medications\Http\Controllers\MedicationReminderController;
use App\Modules\Medications\Http\Controllers\MedicationReminderTimeController;
use App\Modules\Medications\Http\Controllers\MedicationScheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'patient'])->prefix('medications')->group(function (): void {
    Route::get('/reminders', [MedicationReminderController::class, 'index']);
    Route::post('/reminders', [MedicationReminderController::class, 'store'])->middleware('throttle:health-write');
    Route::get('/reminders/{reminder}', [MedicationReminderController::class, 'show']);
    Route::put('/reminders/{reminder}', [MedicationReminderController::class, 'update']);
    Route::delete('/reminders/{reminder}', [MedicationReminderController::class, 'destroy']);

    Route::post('/reminders/{reminder}/pause', [MedicationReminderController::class, 'pause']);
    Route::post('/reminders/{reminder}/resume', [MedicationReminderController::class, 'resume']);
    Route::post('/reminders/{reminder}/cancel', [MedicationReminderController::class, 'cancel']);

    Route::get('/reminders/{reminder}/times', [MedicationReminderTimeController::class, 'index']);
    Route::post('/reminders/{reminder}/times', [MedicationReminderTimeController::class, 'store']);
    Route::put('/reminders/{reminder}/times/{time}', [MedicationReminderTimeController::class, 'update']);
    Route::delete('/reminders/{reminder}/times/{time}', [MedicationReminderTimeController::class, 'destroy']);

    Route::get('/logs', [MedicationLogController::class, 'index']);
    Route::post('/reminders/{reminder}/logs', [MedicationLogController::class, 'store'])->middleware('throttle:health-write');
    Route::put('/logs/{log}', [MedicationLogController::class, 'update']);

    Route::post('/reminders/{reminder}/taken', [MedicationLogController::class, 'taken'])->middleware('throttle:health-write');
    Route::post('/reminders/{reminder}/skipped', [MedicationLogController::class, 'skipped'])->middleware('throttle:health-write');

    Route::get('/reminders/{reminder}/schedule', [MedicationScheduleController::class, 'reminderSchedule']);
    Route::get('/today', [MedicationScheduleController::class, 'today']);
    Route::get('/upcoming', [MedicationScheduleController::class, 'upcoming']);
    Route::get('/adherence', [MedicationScheduleController::class, 'adherence']);

    Route::post('/reminders/{reminder}/refill-done', [MedicationRefillController::class, 'done']);
    Route::post('/reminders/{reminder}/refill-skipped', [MedicationRefillController::class, 'skipped']);
    Route::get('/refills', [MedicationRefillController::class, 'index']);
});

Route::prefix('admin/medications')->middleware(['auth:sanctum', 'admin'])->group(function (): void {
    Route::get('/reminders', [AdminMedicationController::class, 'reminders']);
    Route::get('/reminders/{reminder}', [AdminMedicationController::class, 'reminder']);
    Route::get('/logs', [AdminMedicationController::class, 'logs']);
    Route::get('/refill-events', [AdminMedicationController::class, 'refillEvents']);
    Route::get('/notification-queue', [AdminMedicationController::class, 'notificationQueue']);
});
