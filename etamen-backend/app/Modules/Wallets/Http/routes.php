<?php

use App\Modules\Wallets\Http\Controllers\AdminCommissionRuleController;
use App\Modules\Wallets\Http\Controllers\AdminSettlementController;
use App\Modules\Wallets\Http\Controllers\AdminWalletController;
use App\Modules\Wallets\Http\Controllers\AdminWithdrawalController;
use App\Modules\Wallets\Http\Controllers\ProviderWalletController;
use Illuminate\Support\Facades\Route;

Route::prefix('provider')->middleware(['auth:sanctum', 'provider.user'])->group(function (): void {
    Route::get('/wallet', [ProviderWalletController::class, 'show']);
    Route::get('/wallet/transactions', [ProviderWalletController::class, 'transactions']);
    Route::post('/withdrawals', [ProviderWalletController::class, 'requestWithdrawal']);
    Route::get('/withdrawals', [ProviderWalletController::class, 'withdrawals']);
});

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function (): void {
    Route::get('/wallets', [AdminWalletController::class, 'index']);
    Route::get('/wallets/{wallet}', [AdminWalletController::class, 'show']);
    Route::get('/wallets/{wallet}/transactions', [AdminWalletController::class, 'transactions']);

    Route::get('/commission-rules', [AdminCommissionRuleController::class, 'index']);
    Route::post('/commission-rules', [AdminCommissionRuleController::class, 'store']);
    Route::put('/commission-rules/{rule}', [AdminCommissionRuleController::class, 'update']);

    Route::get('/withdrawals', [AdminWithdrawalController::class, 'index']);
    Route::post('/withdrawals/{withdrawal}/approve', [AdminWithdrawalController::class, 'approve']);
    Route::post('/withdrawals/{withdrawal}/reject', [AdminWithdrawalController::class, 'reject']);
    Route::post('/withdrawals/{withdrawal}/mark-paid', [AdminWithdrawalController::class, 'markPaid']);

    Route::get('/settlements', [AdminSettlementController::class, 'index']);
    Route::post('/settlements', [AdminSettlementController::class, 'store']);
    Route::get('/settlements/{settlement}', [AdminSettlementController::class, 'show']);
    Route::post('/settlements/{settlement}/mark-paid', [AdminSettlementController::class, 'markPaid']);
});
