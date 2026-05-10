<?php

use App\Modules\AdminOperations\Http\Controllers\AdminOperationsController;
use App\Modules\AdminOperations\Http\Controllers\DisputeController;
use App\Modules\AdminOperations\Http\Controllers\RefundController;
use App\Modules\AdminOperations\Http\Controllers\SupportTicketController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/support/tickets', [SupportTicketController::class, 'index']);
    Route::post('/support/tickets', [SupportTicketController::class, 'store'])->middleware('throttle:sensitive-action');
    Route::get('/support/tickets/{ticket}', [SupportTicketController::class, 'show']);
    Route::post('/support/tickets/{ticket}/messages', [SupportTicketController::class, 'message'])->middleware('throttle:sensitive-action');

    Route::get('/refunds', [RefundController::class, 'index']);
    Route::post('/refunds', [RefundController::class, 'store'])->middleware('throttle:sensitive-action');
    Route::get('/refunds/{refund}', [RefundController::class, 'show']);

    Route::get('/disputes', [DisputeController::class, 'index']);
    Route::post('/disputes', [DisputeController::class, 'store'])->middleware('throttle:sensitive-action');
    Route::get('/disputes/{dispute}', [DisputeController::class, 'show']);
});

Route::prefix('admin/operations')->middleware(['auth:sanctum', 'admin'])->group(function (): void {
    Route::get('/dashboard', [AdminOperationsController::class, 'dashboard']);

    Route::get('/payments/pending', [AdminOperationsController::class, 'pendingPayments']);
    Route::get('/payments/{payment}', [AdminOperationsController::class, 'showPayment']);
    Route::post('/payments/{payment}/accept', [AdminOperationsController::class, 'acceptPayment'])->middleware('throttle:admin-sensitive');
    Route::post('/payments/{payment}/reject', [AdminOperationsController::class, 'rejectPayment'])->middleware('throttle:admin-sensitive');

    Route::get('/providers/pending', [AdminOperationsController::class, 'pendingProviders']);
    Route::get('/providers/{provider}', [AdminOperationsController::class, 'showProvider']);
    Route::post('/providers/{provider}/approve', [AdminOperationsController::class, 'approveProvider'])->middleware('throttle:admin-sensitive');
    Route::post('/providers/{provider}/reject', [AdminOperationsController::class, 'rejectProvider'])->middleware('throttle:admin-sensitive');
    Route::post('/providers/{provider}/suspend', [AdminOperationsController::class, 'suspendProvider'])->middleware('throttle:admin-sensitive');

    Route::get('/support/tickets', [AdminOperationsController::class, 'supportTickets']);
    Route::get('/support/tickets/{ticket}', [AdminOperationsController::class, 'showSupportTicket']);
    Route::post('/support/tickets/{ticket}/reply', [AdminOperationsController::class, 'replySupportTicket'])->middleware('throttle:admin-sensitive');
    Route::post('/support/tickets/{ticket}/internal-note', [AdminOperationsController::class, 'internalNoteSupportTicket'])->middleware('throttle:admin-sensitive');
    Route::post('/support/tickets/{ticket}/assign', [AdminOperationsController::class, 'assignSupportTicket'])->middleware('throttle:admin-sensitive');
    Route::post('/support/tickets/{ticket}/close', [AdminOperationsController::class, 'closeSupportTicket'])->middleware('throttle:admin-sensitive');

    Route::get('/refunds', [AdminOperationsController::class, 'refunds']);
    Route::get('/refunds/{refund}', [AdminOperationsController::class, 'showRefund']);
    Route::post('/refunds/{refund}/mark-under-review', [AdminOperationsController::class, 'markRefundUnderReview'])->middleware('throttle:admin-sensitive');
    Route::post('/refunds/{refund}/approve', [AdminOperationsController::class, 'approveRefund'])->middleware('throttle:admin-sensitive');
    Route::post('/refunds/{refund}/reject', [AdminOperationsController::class, 'rejectRefund'])->middleware('throttle:admin-sensitive');
    Route::post('/refunds/{refund}/mark-processed', [AdminOperationsController::class, 'markRefundProcessed'])->middleware('throttle:admin-sensitive');

    Route::get('/disputes', [AdminOperationsController::class, 'disputes']);
    Route::get('/disputes/{dispute}', [AdminOperationsController::class, 'showDispute']);
    Route::post('/disputes/{dispute}/assign', [AdminOperationsController::class, 'assignDispute'])->middleware('throttle:admin-sensitive');
    Route::post('/disputes/{dispute}/resolve', [AdminOperationsController::class, 'resolveDispute'])->middleware('throttle:admin-sensitive');
    Route::post('/disputes/{dispute}/close', [AdminOperationsController::class, 'closeDispute'])->middleware('throttle:admin-sensitive');

    Route::get('/audit-log', [AdminOperationsController::class, 'auditLog']);
});
