<?php

use App\Modules\Patients\Http\Controllers\PatientProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/profile', [PatientProfileController::class, 'show']);
    Route::put('/profile', [PatientProfileController::class, 'update']);
});
