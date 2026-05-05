<?php

namespace App\Modules\Wallets\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Wallets\Application\Services\WalletBalanceService;
use App\Modules\Wallets\Http\Resources\WalletResource;
use App\Modules\Wallets\Http\Resources\WalletTransactionResource;
use App\Modules\Wallets\Infrastructure\Models\Wallet;
use Illuminate\Http\Request;

class AdminWalletController extends ApiController
{
    public function __construct(private readonly WalletBalanceService $balanceService) {}

    public function index(Request $request)
    {
        return $this->success(WalletResource::collection(Wallet::query()->orderByDesc('id')->limit($this->perPage($request, 50))->get()), 'Wallets.');
    }

    public function show(Wallet $wallet)
    {
        return $this->success((new WalletResource($wallet))->additional([
            'balances' => $this->balanceService->summary($wallet),
        ]), 'Wallet details.');
    }

    public function transactions(Request $request, Wallet $wallet)
    {
        return $this->success(
            WalletTransactionResource::collection($wallet->transactions()->orderByDesc('id')->limit($this->perPage($request, 50))->get()),
            'Wallet transactions.',
        );
    }
}
