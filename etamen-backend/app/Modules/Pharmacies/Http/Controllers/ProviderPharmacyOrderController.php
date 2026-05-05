<?php

namespace App\Modules\Pharmacies\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Pharmacies\Application\Services\PharmacyAccessService;
use App\Modules\Pharmacies\Application\Services\PharmacyOrderService;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderStatus;
use App\Modules\Pharmacies\Http\Requests\UpdatePharmacyOrderStatusRequest;
use App\Modules\Pharmacies\Http\Resources\PharmacyOrderResource;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyOrder;
use Illuminate\Http\Request;

class ProviderPharmacyOrderController extends ApiController
{
    public function __construct(
        private readonly PharmacyAccessService $accessService,
        private readonly PharmacyOrderService $orderService,
    ) {}

    public function index(Request $request)
    {
        $provider = $this->accessService->currentPharmacyFor($request->user());

        $orders = PharmacyOrder::query()
            ->where('pharmacy_provider_id', $provider->id)
            ->with(['items', 'patient', 'payment'])
            ->when($request->query('status'), fn ($query, $status) => $query->where('order_status', $status))
            ->orderByDesc('id')
            ->get();

        return $this->success(PharmacyOrderResource::collection($orders), 'Provider pharmacy orders.');
    }

    public function show(PharmacyOrder $order)
    {
        $this->authorize('providerView', $order);

        return $this->success(new PharmacyOrderResource($order->load(['items', 'payment.paymentMethod', 'prescription.uploadedFile', 'statusHistories'])), 'Provider pharmacy order details.');
    }

    public function updateStatus(UpdatePharmacyOrderStatusRequest $request, PharmacyOrder $order)
    {
        $this->authorize('providerManage', $order);
        $order = $this->orderService->providerUpdateStatus(
            $request->user(),
            $order,
            PharmacyOrderStatus::from($request->validated('status')),
            $request->validated('reason'),
        );

        return $this->success(new PharmacyOrderResource($order), 'Pharmacy order status updated.');
    }
}
