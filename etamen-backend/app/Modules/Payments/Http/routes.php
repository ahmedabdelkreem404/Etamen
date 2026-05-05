<?php

use App\Modules\Payments\Http\Controllers\AdminPaymentController;
use App\Modules\Payments\Http\Controllers\ManualPaymentController;
use App\Modules\Payments\Http\Controllers\PaymentMethodController;
use App\Modules\Payments\Http\Controllers\PaymentStatusController;
use App\Modules\Payments\Http\Controllers\PaymobPaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/payment-methods', [PaymentMethodController::class, 'index'])
    ->middleware('throttle:60,1');

Route::post('/payments/paymob/callback', [PaymobPaymentController::class, 'callback'])
    ->middleware('throttle:120,1');
Route::post('/payments/paymob/webhook', [PaymobPaymentController::class, 'webhook'])
    ->middleware('throttle:120,1');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/payments/{payment}/status', [PaymentStatusController::class, 'show']);
    Route::post('/payments/{payment}/paymob/create-session', [PaymobPaymentController::class, 'createSession']);
    Route::post('/payments/{payment}/manual/select', [ManualPaymentController::class, 'select']);
    Route::post('/payments/{payment}/proofs', [ManualPaymentController::class, 'uploadProof']);
});

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function (): void {
    Route::get('/payments', [AdminPaymentController::class, 'index']);
    Route::get('/payments/pending-review', [AdminPaymentController::class, 'pendingReview']);
    Route::get('/payments/{payment}', [AdminPaymentController::class, 'show']);
    Route::post('/payments/{payment}/accept', [AdminPaymentController::class, 'accept']);
    Route::post('/payments/{payment}/reject', [AdminPaymentController::class, 'reject']);
    Route::get('/invoices', [AdminPaymentController::class, 'invoices']);
});
