<?php

use App\Modules\System\Http\Controllers\SystemHealthController;
use Illuminate\Support\Facades\Route;

Route::get('/system/health', [SystemHealthController::class, 'health'])
    ->middleware('throttle:60,1');

Route::get('/system/readiness', [SystemHealthController::class, 'readiness'])
    ->middleware(['auth:sanctum', 'admin', 'throttle:30,1']);
