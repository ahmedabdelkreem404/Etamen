<?php

use App\Modules\Payments\Http\Controllers\PaymentMethodController;
use App\Modules\Payments\Http\Controllers\PaymentStatusController;
use Illuminate\Support\Facades\Route;

Route::get('/payment-methods', [PaymentMethodController::class, 'index'])
    ->middleware('throttle:60,1');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/payments/{payment}/status', [PaymentStatusController::class, 'show']);
});
