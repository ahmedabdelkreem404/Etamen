<?php

namespace App\Modules\Pharmacies\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Pharmacies\Application\Services\PharmacyOrderService;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderStatus;
use App\Modules\Pharmacies\Http\Requests\UpdatePharmacyOrderStatusRequest;
use App\Modules\Pharmacies\Http\Resources\PharmacyOrderResource;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyOrder;
use Illuminate\Http\Request;

class AdminPharmacyOrderController extends ApiController
{
    public function __construct(private readonly PharmacyOrderService $orderService) {}

    public function index(Request $request)
    {
        $orders = PharmacyOrder::query()
            ->with(['items', 'pharmacy', 'patient', 'payment'])
            ->when($request->query('status'), fn ($query, $status) => $query->where('order_status', $status))
            ->when($request->query('payment_status'), fn ($query, $status) => $query->where('payment_status', $status))
            ->when($request->query('pharmacy_provider_id'), fn ($query, $providerId) => $query->where('pharmacy_provider_id', $providerId))
            ->orderByDesc('id')
            ->get();

        return $this->success(PharmacyOrderResource::collection($orders), 'Admin pharmacy orders.');
    }

    public function show(PharmacyOrder $order)
    {
        return $this->success(new PharmacyOrderResource($order->load(['items', 'pharmacy', 'patient', 'payment.paymentMethod', 'prescription.uploadedFile', 'statusHistories'])), 'Admin pharmacy order details.');
    }

    public function updateStatus(UpdatePharmacyOrderStatusRequest $request, PharmacyOrder $order)
    {
        $order = $this->orderService->adminUpdateStatus(
            $request->user(),
            $order,
            PharmacyOrderStatus::from($request->validated('status')),
            $request->validated('reason'),
        );

        return $this->success(new PharmacyOrderResource($order), 'Admin pharmacy order status updated.');
    }
}
