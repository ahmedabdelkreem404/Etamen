<?php

use App\Modules\Locations\Http\Controllers\LocationController;
use Illuminate\Support\Facades\Route;

Route::get('/cities', [LocationController::class, 'cities'])->middleware('throttle:60,1');
Route::get('/areas', [LocationController::class, 'areas'])->middleware('throttle:60,1');
