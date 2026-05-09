<?php

namespace App\Modules\Radiology\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Radiology\Application\Services\RadiologyAccessService;
use App\Modules\Radiology\Application\Services\RadiologyOrderService;
use App\Modules\Radiology\Application\Services\RadiologyResultService;
use App\Modules\Radiology\Http\Requests\RejectRadiologyOrderRequest;
use App\Modules\Radiology\Http\Requests\UploadRadiologyResultRequest;
use App\Modules\Radiology\Http\Resources\RadiologyOrderResource;
use App\Modules\Radiology\Http\Resources\RadiologyResultResource;
use App\Modules\Radiology\Infrastructure\Models\RadiologyOrder;
use Illuminate\Http\Request;

class ProviderRadiologyOrderController extends ApiController
{
    public function __construct(
        private readonly RadiologyAccessService $accessService,
        private readonly RadiologyOrderService $orderService,
        private readonly RadiologyResultService $resultService,
    ) {}

    public function index(Request $request)
    {
        $provider = $this->accessService->currentRadiologyFor($request->user());
        $orders = RadiologyOrder::query()
            ->where('provider_id', $provider->id)
            ->with(['items', 'patient', 'payment.paymentMethod'])
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('id')
            ->limit($this->perPage($request))
            ->get();

        return $this->success(RadiologyOrderResource::collection($orders), 'Provider radiology orders.');
    }

    public function show(RadiologyOrder $order)
    {
        $this->authorize('providerView', $order);

        return $this->success(
            new RadiologyOrderResource($order->load(['items', 'provider', 'branch', 'payment.paymentMethod', 'results.file', 'statusHistories'])),
            'Provider radiology order details.',
        );
    }

    public function accept(Request $request, RadiologyOrder $order)
    {
        $this->authorize('providerManage', $order);
        $order = $this->orderService->accept($request->user(), $order);

        return $this->success(new RadiologyOrderResource($order), 'Radiology order accepted.');
    }

    public function reject(RejectRadiologyOrderRequest $request, RadiologyOrder $order)
    {
        $this->authorize('providerManage', $order);
        $order = $this->orderService->reject($request->user(), $order, $request->validated('reason'));

        return $this->success(new RadiologyOrderResource($order), 'Radiology order rejected.');
    }

    public function start(Request $request, RadiologyOrder $order)
    {
        $this->authorize('providerManage', $order);
        $order = $this->orderService->start($request->user(), $order);

        return $this->success(new RadiologyOrderResource($order), 'Radiology order started.');
    }

    public function markResultReady(Request $request, RadiologyOrder $order)
    {
        $this->authorize('providerManage', $order);
        $order = $this->orderService->markResultReady($request->user(), $order);

        return $this->success(new RadiologyOrderResource($order), 'Radiology result is ready.');
    }

    public function complete(Request $request, RadiologyOrder $order)
    {
        $this->authorize('providerManage', $order);
        $order = $this->orderService->complete($request->user(), $order);

        return $this->success(new RadiologyOrderResource($order), 'Radiology order completed.');
    }

    public function uploadResult(UploadRadiologyResultRequest $request, RadiologyOrder $order)
    {
        $this->authorize('uploadResult', $order);
        $result = $this->resultService->upload($request->user(), $order, $request->file('file'), $request->validated());

        return $this->success(new RadiologyResultResource($result), 'Radiology result uploaded.', 201);
    }
}
