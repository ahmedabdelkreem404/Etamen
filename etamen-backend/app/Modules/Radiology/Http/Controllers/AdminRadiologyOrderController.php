<?php

namespace App\Modules\Radiology\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Radiology\Application\Services\RadiologyOrderService;
use App\Modules\Radiology\Application\Services\RadiologyResultService;
use App\Modules\Radiology\Http\Requests\CancelRadiologyOrderRequest;
use App\Modules\Radiology\Http\Requests\UploadRadiologyResultRequest;
use App\Modules\Radiology\Http\Resources\RadiologyOrderResource;
use App\Modules\Radiology\Http\Resources\RadiologyResultResource;
use App\Modules\Radiology\Infrastructure\Models\RadiologyOrder;
use Illuminate\Http\Request;

class AdminRadiologyOrderController extends ApiController
{
    public function __construct(
        private readonly RadiologyOrderService $orderService,
        private readonly RadiologyResultService $resultService,
    ) {}

    public function index(Request $request)
    {
        $orders = RadiologyOrder::query()
            ->with(['items', 'provider', 'branch', 'patient', 'payment.paymentMethod'])
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('provider_id'), fn ($query, $providerId) => $query->where('provider_id', $providerId))
            ->when($request->query('patient_user_id'), fn ($query, $patientId) => $query->where('patient_user_id', $patientId))
            ->when($request->query('date_from'), fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($request->query('date_to'), fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->orderByDesc('id')
            ->limit($this->perPage($request))
            ->get();

        return $this->success(RadiologyOrderResource::collection($orders), 'Admin radiology orders.');
    }

    public function show(RadiologyOrder $order)
    {
        return $this->success(
            new RadiologyOrderResource($order->load(['items', 'provider', 'branch', 'patient', 'payment.paymentMethod', 'results.file', 'statusHistories'])),
            'Admin radiology order details.',
        );
    }

    public function forceCancel(CancelRadiologyOrderRequest $request, RadiologyOrder $order)
    {
        $order = $this->orderService->forceCancel($request->user(), $order, $request->validated('reason'));

        return $this->success(new RadiologyOrderResource($order), 'Radiology order force-cancelled.');
    }

    public function statusHistory(RadiologyOrder $order)
    {
        return $this->success($order->statusHistories()->latest('id')->get(), 'Radiology order status history.');
    }

    public function uploadResult(UploadRadiologyResultRequest $request, RadiologyOrder $order)
    {
        $result = $this->resultService->upload($request->user(), $order, $request->file('file'), $request->validated());

        return $this->success(new RadiologyResultResource($result), 'Radiology result uploaded.', 201);
    }
}
