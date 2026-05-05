<?php

namespace App\Modules\Wallets\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Providers\Domain\Enums\ProviderType;
use App\Modules\Wallets\Application\Services\SettlementService;
use App\Modules\Wallets\Http\Requests\StoreSettlementRequest;
use App\Modules\Wallets\Http\Resources\SettlementResource;
use App\Modules\Wallets\Infrastructure\Models\Settlement;
use Illuminate\Http\Request;

class AdminSettlementController extends ApiController
{
    public function __construct(private readonly SettlementService $settlementService) {}

    public function index()
    {
        return $this->success(SettlementResource::collection(Settlement::query()->with('items')->orderByDesc('id')->get()), 'Settlements.');
    }

    public function store(StoreSettlementRequest $request)
    {
        $settlement = $this->settlementService->create(
            $request->user(),
            (int) $request->validated('provider_id'),
            ProviderType::from($request->validated('provider_type')),
        );

        return $this->success(new SettlementResource($settlement), 'Settlement created.', 201);
    }

    public function show(Settlement $settlement)
    {
        return $this->success(new SettlementResource($settlement->load('items')), 'Settlement details.');
    }

    public function markPaid(Request $request, Settlement $settlement)
    {
        $settlement = $this->settlementService->markPaid($request->user(), $settlement);

        return $this->success(new SettlementResource($settlement), 'Settlement marked paid.');
    }
}
