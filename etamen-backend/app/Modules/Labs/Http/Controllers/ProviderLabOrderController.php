<?php

namespace App\Modules\Labs\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Labs\Application\Services\LabAccessService;
use App\Modules\Labs\Application\Services\LabOrderService;
use App\Modules\Labs\Application\Services\LabResultService;
use App\Modules\Labs\Domain\Enums\LabOrderStatus;
use App\Modules\Labs\Http\Requests\UpdateLabOrderStatusRequest;
use App\Modules\Labs\Http\Requests\UploadLabResultRequest;
use App\Modules\Labs\Http\Resources\LabOrderResource;
use App\Modules\Labs\Http\Resources\LabResultResource;
use App\Modules\Labs\Infrastructure\Models\LabOrder;
use Illuminate\Http\Request;

class ProviderLabOrderController extends ApiController
{
    public function __construct(
        private readonly LabAccessService $accessService,
        private readonly LabOrderService $orderService,
        private readonly LabResultService $resultService,
    ) {}

    public function index(Request $request)
    {
        $lab = $this->accessService->currentLabFor($request->user());
        $orders = LabOrder::query()
            ->where('lab_provider_id', $lab->id)
            ->with(['items', 'patient', 'payment'])
            ->when($request->query('status'), fn ($query, $status) => $query->where('order_status', $status))
            ->orderByDesc('id')
            ->get();

        return $this->success(LabOrderResource::collection($orders), 'Provider lab orders.');
    }

    public function show(LabOrder $order)
    {
        $this->authorize('providerView', $order);

        return $this->success(new LabOrderResource($order->load(['items', 'payment.paymentMethod', 'results.file', 'statusHistories'])), 'Provider lab order details.');
    }

    public function updateStatus(UpdateLabOrderStatusRequest $request, LabOrder $order)
    {
        $this->authorize('providerManage', $order);
        $order = $this->orderService->providerUpdateStatus(
            $request->user(),
            $order,
            LabOrderStatus::from($request->validated('status')),
            $request->validated('reason'),
        );

        return $this->success(new LabOrderResource($order), 'Lab order status updated.');
    }

    public function uploadResult(UploadLabResultRequest $request, LabOrder $order)
    {
        $this->authorize('uploadResult', $order);
        $result = $this->resultService->upload(
            $request->user(),
            $order,
            $request->file('file'),
            $request->validated(),
        );

        return $this->success(new LabResultResource($result), 'Lab result uploaded.', 201);
    }
}
