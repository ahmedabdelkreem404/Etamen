<?php

namespace App\Modules\Wallets\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Wallets\Application\Services\WithdrawalService;
use App\Modules\Wallets\Http\Requests\RejectWithdrawalRequest;
use App\Modules\Wallets\Http\Resources\WithdrawalRequestResource;
use App\Modules\Wallets\Infrastructure\Models\WithdrawalRequest;
use Illuminate\Http\Request;

class AdminWithdrawalController extends ApiController
{
    public function __construct(private readonly WithdrawalService $withdrawalService) {}

    public function index()
    {
        return $this->success(
            WithdrawalRequestResource::collection(WithdrawalRequest::query()->with('wallet')->orderByDesc('id')->get()),
            'Withdrawal requests.',
        );
    }

    public function approve(Request $request, WithdrawalRequest $withdrawal)
    {
        $withdrawal = $this->withdrawalService->approve($request->user(), $withdrawal);

        return $this->success(new WithdrawalRequestResource($withdrawal), 'Withdrawal approved.');
    }

    public function reject(RejectWithdrawalRequest $request, WithdrawalRequest $withdrawal)
    {
        $withdrawal = $this->withdrawalService->reject($request->user(), $withdrawal, $request->validated('reason'));

        return $this->success(new WithdrawalRequestResource($withdrawal), 'Withdrawal rejected.');
    }

    public function markPaid(Request $request, WithdrawalRequest $withdrawal)
    {
        $withdrawal = $this->withdrawalService->markPaid($request->user(), $withdrawal);

        return $this->success(new WithdrawalRequestResource($withdrawal), 'Withdrawal marked as paid.');
    }
}
