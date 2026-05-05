<?php

namespace App\Modules\Labs\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Labs\Application\Services\LabOrderService;
use App\Modules\Labs\Domain\Enums\LabOrderStatus;
use App\Modules\Labs\Http\Requests\UpdateLabOrderStatusRequest;
use App\Modules\Labs\Http\Resources\LabOrderResource;
use App\Modules\Labs\Infrastructure\Models\LabOrder;
use Illuminate\Http\Request;

class AdminLabOrderController extends ApiController
{
    public function __construct(private readonly LabOrderService $orderService) {}

    public function index(Request $request)
    {
        $orders = LabOrder::query()
            ->with(['items', 'lab', 'patient', 'payment'])
            ->when($request->query('status'), fn ($query, $status) => $query->where('order_status', $status))
            ->when($request->query('payment_status'), fn ($query, $status) => $query->where('payment_status', $status))
            ->when($request->query('lab_provider_id'), fn ($query, $providerId) => $query->where('lab_provider_id', $providerId))
            ->orderByDesc('id')
            ->get();

        return $this->success(LabOrderResource::collection($orders), 'Admin lab orders.');
    }

    public function show(LabOrder $order)
    {
        return $this->success(new LabOrderResource($order->load(['items', 'lab', 'patient', 'payment.paymentMethod', 'results.file', 'statusHistories'])), 'Admin lab order details.');
    }

    public function updateStatus(UpdateLabOrderStatusRequest $request, LabOrder $order)
    {
        $order = $this->orderService->adminUpdateStatus(
            $request->user(),
            $order,
            LabOrderStatus::from($request->validated('status')),
            $request->validated('reason'),
        );

        return $this->success(new LabOrderResource($order), 'Admin lab order status updated.');
    }
}
