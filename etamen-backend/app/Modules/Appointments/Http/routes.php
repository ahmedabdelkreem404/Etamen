<?php

use App\Modules\Appointments\Http\Controllers\AdminAppointmentController;
use App\Modules\Appointments\Http\Controllers\PatientAppointmentController;
use App\Modules\Appointments\Http\Controllers\ProviderAppointmentController;
use App\Modules\Appointments\Http\Controllers\ProviderDoctorScheduleController;
use App\Modules\Appointments\Http\Controllers\PublicDoctorSlotController;
use Illuminate\Support\Facades\Route;

Route::get('/doctors/{doctor}/slots', [PublicDoctorSlotController::class, 'index']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/appointments', [PatientAppointmentController::class, 'store']);
    Route::get('/appointments', [PatientAppointmentController::class, 'index']);
    Route::get('/appointments/{appointment}', [PatientAppointmentController::class, 'show']);
    Route::post('/appointments/{appointment}/cancel', [PatientAppointmentController::class, 'cancel']);
    Route::post('/appointments/{appointment}/review', [PatientAppointmentController::class, 'review']);
});

Route::prefix('provider')->middleware(['auth:sanctum', 'provider.user'])->group(function (): void {
    Route::post('/doctor/schedules', [ProviderDoctorScheduleController::class, 'store']);
    Route::get('/doctor/schedules', [ProviderDoctorScheduleController::class, 'index']);
    Route::put('/doctor/schedules/{schedule}', [ProviderDoctorScheduleController::class, 'update']);
    Route::post('/doctor/schedules/{schedule}/days', [ProviderDoctorScheduleController::class, 'addDay']);
    Route::put('/doctor/schedule-days/{day}', [ProviderDoctorScheduleController::class, 'updateDay']);
    Route::post('/doctor/holidays', [ProviderDoctorScheduleController::class, 'storeHoliday']);
    Route::get('/doctor/holidays', [ProviderDoctorScheduleController::class, 'holidays']);
    Route::post('/doctor/slots/generate', [ProviderDoctorScheduleController::class, 'generateSlots']);

    Route::get('/appointments', [ProviderAppointmentController::class, 'index']);
    Route::get('/appointments/{appointment}', [ProviderAppointmentController::class, 'show']);
    Route::post('/appointments/{appointment}/accept', [ProviderAppointmentController::class, 'accept']);
    Route::post('/appointments/{appointment}/reject', [ProviderAppointmentController::class, 'reject']);
    Route::post('/appointments/{appointment}/complete', [ProviderAppointmentController::class, 'complete']);
    Route::post('/appointments/{appointment}/no-show', [ProviderAppointmentController::class, 'noShow']);
});

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function (): void {
    Route::get('/appointments', [AdminAppointmentController::class, 'index']);
    Route::get('/appointments/{appointment}', [AdminAppointmentController::class, 'show']);
    Route::get('/appointments/{appointment}/status-history', [AdminAppointmentController::class, 'statusHistory']);
    Route::post('/appointments/{appointment}/force-cancel', [AdminAppointmentController::class, 'forceCancel']);
});
