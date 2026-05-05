<?php

use App\Modules\Notifications\Http\Controllers\AdminNotificationController;
use App\Modules\Notifications\Http\Controllers\NotificationController;
use App\Modules\Notifications\Http\Controllers\NotificationPreferenceController;
use App\Modules\Notifications\Http\Controllers\NotificationTokenController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::get('/notifications/{notification}', [NotificationController::class, 'show']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->middleware('throttle:notification-write');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->middleware('throttle:notification-write');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);

    Route::get('/notification-tokens', [NotificationTokenController::class, 'index']);
    Route::post('/notification-tokens', [NotificationTokenController::class, 'store'])->middleware('throttle:notification-write');
    Route::delete('/notification-tokens/{token}', [NotificationTokenController::class, 'destroy'])->middleware('throttle:notification-write');

    Route::get('/notification-preferences', [NotificationPreferenceController::class, 'index']);
    Route::put('/notification-preferences', [NotificationPreferenceController::class, 'update'])->middleware('throttle:notification-write');
});

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function (): void {
    Route::get('/notifications', [AdminNotificationController::class, 'notifications']);
    Route::get('/notification-dispatches', [AdminNotificationController::class, 'dispatches']);
    Route::get('/notification-templates', [AdminNotificationController::class, 'templates']);
    Route::post('/notification-templates', [AdminNotificationController::class, 'storeTemplate'])->middleware('throttle:admin-sensitive');
    Route::put('/notification-templates/{template}', [AdminNotificationController::class, 'updateTemplate'])->middleware('throttle:admin-sensitive');
    Route::get('/scheduler-runs', [AdminNotificationController::class, 'schedulerRuns']);
    Route::get('/notification-tokens', [AdminNotificationController::class, 'tokens']);
});
