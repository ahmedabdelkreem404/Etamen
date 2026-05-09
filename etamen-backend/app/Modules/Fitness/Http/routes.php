<?php

use App\Modules\Fitness\Http\Controllers\AdminFitnessController;
use App\Modules\Fitness\Http\Controllers\PatientCoachBookingController;
use App\Modules\Fitness\Http\Controllers\PatientGymBookingController;
use App\Modules\Fitness\Http\Controllers\ProviderCoachController;
use App\Modules\Fitness\Http\Controllers\ProviderGymController;
use App\Modules\Fitness\Http\Controllers\PublicCoachController;
use App\Modules\Fitness\Http\Controllers\PublicGymController;
use Illuminate\Support\Facades\Route;

Route::get('/gyms', [PublicGymController::class, 'index']);
Route::get('/gyms/{gym}', [PublicGymController::class, 'show']);
Route::get('/gyms/{gym}/membership-plans', [PublicGymController::class, 'membershipPlans']);
Route::get('/gyms/{gym}/classes', [PublicGymController::class, 'classes']);

Route::get('/coaches', [PublicCoachController::class, 'index']);
Route::get('/coaches/{coach}', [PublicCoachController::class, 'show']);
Route::get('/coaches/{coach}/session-types', [PublicCoachController::class, 'sessionTypes']);
Route::get('/coaches/{coach}/availability', [PublicCoachController::class, 'availability']);
Route::get('/coaches/{coach}/packages', [PublicCoachController::class, 'packages']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/gym/bookings', [PatientGymBookingController::class, 'index']);
    Route::post('/gym/bookings', [PatientGymBookingController::class, 'store'])->middleware('throttle:sensitive-action');
    Route::get('/gym/bookings/{booking}', [PatientGymBookingController::class, 'show']);
    Route::post('/gym/bookings/{booking}/cancel', [PatientGymBookingController::class, 'cancel'])->middleware('throttle:sensitive-action');

    Route::get('/coach/bookings', [PatientCoachBookingController::class, 'index']);
    Route::post('/coach/bookings', [PatientCoachBookingController::class, 'store'])->middleware('throttle:sensitive-action');
    Route::get('/coach/bookings/{booking}', [PatientCoachBookingController::class, 'show']);
    Route::post('/coach/bookings/{booking}/cancel', [PatientCoachBookingController::class, 'cancel'])->middleware('throttle:sensitive-action');
});

Route::prefix('provider/gym')->middleware(['auth:sanctum', 'provider.user'])->group(function (): void {
    Route::get('/membership-plans', [ProviderGymController::class, 'plans']);
    Route::post('/membership-plans', [ProviderGymController::class, 'storePlan'])->middleware('throttle:sensitive-action');
    Route::patch('/membership-plans/{plan}', [ProviderGymController::class, 'updatePlan'])->middleware('throttle:sensitive-action');
    Route::delete('/membership-plans/{plan}', [ProviderGymController::class, 'destroyPlan'])->middleware('throttle:sensitive-action');

    Route::get('/classes', [ProviderGymController::class, 'classes']);
    Route::post('/classes', [ProviderGymController::class, 'storeClass'])->middleware('throttle:sensitive-action');
    Route::patch('/classes/{class}', [ProviderGymController::class, 'updateClass'])->middleware('throttle:sensitive-action');
    Route::delete('/classes/{class}', [ProviderGymController::class, 'destroyClass'])->middleware('throttle:sensitive-action');

    Route::get('/bookings', [ProviderGymController::class, 'bookings']);
});

Route::prefix('provider/coach')->middleware(['auth:sanctum', 'provider.user'])->group(function (): void {
    Route::get('/session-types', [ProviderCoachController::class, 'sessionTypes']);
    Route::post('/session-types', [ProviderCoachController::class, 'storeSessionType'])->middleware('throttle:sensitive-action');
    Route::patch('/session-types/{sessionType}', [ProviderCoachController::class, 'updateSessionType'])->middleware('throttle:sensitive-action');
    Route::delete('/session-types/{sessionType}', [ProviderCoachController::class, 'destroySessionType'])->middleware('throttle:sensitive-action');

    Route::get('/availability', [ProviderCoachController::class, 'availability']);
    Route::post('/availability', [ProviderCoachController::class, 'storeAvailability'])->middleware('throttle:sensitive-action');
    Route::patch('/availability/{slot}', [ProviderCoachController::class, 'updateAvailability'])->middleware('throttle:sensitive-action');

    Route::get('/packages', [ProviderCoachController::class, 'packages']);
    Route::post('/packages', [ProviderCoachController::class, 'storePackage'])->middleware('throttle:sensitive-action');
    Route::patch('/packages/{package}', [ProviderCoachController::class, 'updatePackage'])->middleware('throttle:sensitive-action');
    Route::delete('/packages/{package}', [ProviderCoachController::class, 'destroyPackage'])->middleware('throttle:sensitive-action');

    Route::get('/bookings', [ProviderCoachController::class, 'bookings']);
});

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function (): void {
    Route::get('/gym-bookings', [AdminFitnessController::class, 'gymBookings']);
    Route::get('/gym-bookings/{booking}', [AdminFitnessController::class, 'gymBooking']);
    Route::get('/coach-bookings', [AdminFitnessController::class, 'coachBookings']);
    Route::get('/coach-bookings/{booking}', [AdminFitnessController::class, 'coachBooking']);
});
