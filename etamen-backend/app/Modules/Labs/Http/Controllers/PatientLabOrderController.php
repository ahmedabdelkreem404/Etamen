<?php

namespace App\Modules\Labs\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Labs\Application\Services\LabOrderService;
use App\Modules\Labs\Domain\Enums\LabResultStatus;
use App\Modules\Labs\Http\Requests\CreateLabOrderRequest;
use App\Modules\Labs\Http\Resources\LabOrderResource;
use App\Modules\Labs\Http\Resources\LabResultResource;
use App\Modules\Labs\Infrastructure\Models\LabOrder;
use Illuminate\Http\Request;

class PatientLabOrderController extends ApiController
{
    public function __construct(private readonly LabOrderService $orderService) {}

    public function store(CreateLabOrderRequest $request)
    {
        $order = $this->orderService->create($request->user(), $request->validated());

        return $this->success(new LabOrderResource($order), 'Lab order created.', 201);
    }

    public function index(Request $request)
    {
        $orders = LabOrder::query()
            ->where('patient_user_id', $request->user()->id)
            ->with(['items', 'lab', 'payment'])
            ->orderByDesc('id')
            ->get();

        return $this->success(LabOrderResource::collection($orders), 'Patient lab orders.');
    }

    public function show(LabOrder $order)
    {
        $this->authorize('view', $order);

        return $this->success(
            new LabOrderResource($order->load(['items', 'lab', 'payment.paymentMethod', 'results.file', 'statusHistories'])),
            'Lab order details.',
        );
    }

    public function pay(Request $request, LabOrder $order)
    {
        $this->authorize('pay', $order);
        $order = $this->orderService->createPayment($request->user(), $order);

        return $this->success(new LabOrderResource($order->load(['items', 'payment.paymentMethod'])), 'Lab order payment created.');
    }

    public function results(LabOrder $order)
    {
        $this->authorize('view', $order);
        $results = $order->results()
            ->where('status', LabResultStatus::VisibleToPatient)
            ->with('file')
            ->latest('id')
            ->get();

        return $this->success(LabResultResource::collection($results), 'Lab order results.');
    }
}
