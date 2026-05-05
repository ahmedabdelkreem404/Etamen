<?php

namespace App\Modules\Wallets\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Wallets\Application\Services\WalletBalanceService;
use App\Modules\Wallets\Http\Resources\WalletResource;
use App\Modules\Wallets\Http\Resources\WalletTransactionResource;
use App\Modules\Wallets\Infrastructure\Models\Wallet;

class AdminWalletController extends ApiController
{
    public function __construct(private readonly WalletBalanceService $balanceService) {}

    public function index()
    {
        return $this->success(WalletResource::collection(Wallet::query()->orderByDesc('id')->get()), 'Wallets.');
    }

    public function show(Wallet $wallet)
    {
        return $this->success((new WalletResource($wallet))->additional([
            'balances' => $this->balanceService->summary($wallet),
        ]), 'Wallet details.');
    }

    public function transactions(Wallet $wallet)
    {
        return $this->success(
            WalletTransactionResource::collection($wallet->transactions()->orderByDesc('id')->get()),
            'Wallet transactions.',
        );
    }
}
