<?php

namespace App\Modules\Wallets\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Providers\Application\Services\ProviderProfileService;
use App\Modules\Wallets\Application\Services\WalletBalanceService;
use App\Modules\Wallets\Application\Services\WalletService;
use App\Modules\Wallets\Application\Services\WithdrawalService;
use App\Modules\Wallets\Http\Requests\WithdrawalRequest;
use App\Modules\Wallets\Http\Resources\WalletResource;
use App\Modules\Wallets\Http\Resources\WalletTransactionResource;
use App\Modules\Wallets\Http\Resources\WithdrawalRequestResource;
use Illuminate\Http\Request;

class ProviderWalletController extends ApiController
{
    public function __construct(
        private readonly ProviderProfileService $providerProfileService,
        private readonly WalletService $walletService,
        private readonly WalletBalanceService $balanceService,
        private readonly WithdrawalService $withdrawalService,
    ) {}

    public function show(Request $request)
    {
        $wallet = $this->currentWallet($request);

        return $this->success((new WalletResource($wallet))->additional([
            'balances' => $this->balanceService->summary($wallet),
        ]), 'Provider wallet.');
    }

    public function transactions(Request $request)
    {
        $wallet = $this->currentWallet($request);

        return $this->success(
            WalletTransactionResource::collection($wallet->transactions()->orderByDesc('id')->get()),
            'Wallet transactions.',
        );
    }

    public function requestWithdrawal(WithdrawalRequest $request)
    {
        $wallet = $this->currentWallet($request);
        $withdrawal = $this->withdrawalService->request($request->user(), $wallet, (float) $request->validated('amount'));

        return $this->success(new WithdrawalRequestResource($withdrawal), 'Withdrawal requested.', 201);
    }

    public function withdrawals(Request $request)
    {
        $wallet = $this->currentWallet($request);

        return $this->success(
            WithdrawalRequestResource::collection($wallet->withdrawalRequests()->orderByDesc('id')->get()),
            'Provider withdrawals.',
        );
    }

    private function currentWallet(Request $request)
    {
        $provider = $this->providerProfileService->currentProviderFor($request->user());

        return $this->walletService->walletForProvider($provider);
    }
}
