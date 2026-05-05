<?php

use App\Modules\AI\Http\Controllers\AdminAiController;
use App\Modules\AI\Http\Controllers\AiContextController;
use App\Modules\AI\Http\Controllers\AiConversationController;
use App\Modules\AI\Http\Controllers\AiMessageController;
use App\Modules\AI\Http\Controllers\AiQuickAskController;
use Illuminate\Support\Facades\Route;

Route::prefix('ai')->middleware(['auth:sanctum', 'patient'])->group(function (): void {
    Route::get('/conversations', [AiConversationController::class, 'index']);
    Route::post('/conversations', [AiConversationController::class, 'store']);
    Route::get('/conversations/{conversation}', [AiConversationController::class, 'show']);
    Route::put('/conversations/{conversation}', [AiConversationController::class, 'update']);
    Route::delete('/conversations/{conversation}', [AiConversationController::class, 'destroy']);

    Route::post('/conversations/{conversation}/messages', [AiMessageController::class, 'store'])->middleware('throttle:ai-message');
    Route::get('/conversations/{conversation}/messages', [AiMessageController::class, 'index']);

    Route::post('/ask', AiQuickAskController::class)->middleware('throttle:ai-message');

    Route::get('/context-preview', [AiContextController::class, 'preview']);
    Route::post('/conversations/{conversation}/toggle-context', [AiContextController::class, 'toggle']);
});

Route::prefix('admin/ai')->middleware(['auth:sanctum', 'admin'])->group(function (): void {
    Route::get('/conversations', [AdminAiController::class, 'conversations']);
    Route::get('/conversations/{conversation}', [AdminAiController::class, 'conversation']);
    Route::get('/messages', [AdminAiController::class, 'messages']);
    Route::get('/safety-events', [AdminAiController::class, 'safetyEvents']);
    Route::get('/usage-logs', [AdminAiController::class, 'usageLogs']);
    Route::get('/provider-configs', [AdminAiController::class, 'providerConfigs']);
    Route::put('/provider-configs/{config}', [AdminAiController::class, 'updateProviderConfig']);
});
