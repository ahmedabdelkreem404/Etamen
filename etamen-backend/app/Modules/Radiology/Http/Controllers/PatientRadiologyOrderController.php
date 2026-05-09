<?php

namespace App\Modules\Radiology\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Radiology\Application\Services\RadiologyOrderService;
use App\Modules\Radiology\Http\Requests\CancelRadiologyOrderRequest;
use App\Modules\Radiology\Http\Requests\CreateRadiologyOrderRequest;
use App\Modules\Radiology\Http\Resources\RadiologyOrderResource;
use App\Modules\Radiology\Http\Resources\RadiologyResultResource;
use App\Modules\Radiology\Infrastructure\Models\RadiologyOrder;
use Illuminate\Http\Request;

class PatientRadiologyOrderController extends ApiController
{
    public function __construct(private readonly RadiologyOrderService $orderService) {}

    public function index(Request $request)
    {
        $orders = RadiologyOrder::query()
            ->where('patient_user_id', $request->user()->id)
            ->with(['items', 'provider', 'branch', 'payment.paymentMethod'])
            ->orderByDesc('id')
            ->limit($this->perPage($request))
            ->get();

        return $this->success(RadiologyOrderResource::collection($orders), 'Patient radiology orders.');
    }

    public function store(CreateRadiologyOrderRequest $request)
    {
        $order = $this->orderService->create($request->user(), $request->validated());

        return $this->success(new RadiologyOrderResource($order), 'Radiology order created.', 201);
    }

    public function show(RadiologyOrder $order)
    {
        $this->authorize('view', $order);

        $order->load([
            'items',
            'provider',
            'branch',
            'payment.paymentMethod',
            'results' => fn ($query) => $query->where('is_visible_to_patient', true),
            'results.file',
        ]);

        return $this->success(new RadiologyOrderResource($order), 'Radiology order details.');
    }

    public function cancel(CancelRadiologyOrderRequest $request, RadiologyOrder $order)
    {
        $this->authorize('cancel', $order);
        $order = $this->orderService->cancelByPatient($request->user(), $order, $request->validated('reason'));

        return $this->success(new RadiologyOrderResource($order), 'Radiology order cancelled.');
    }

    public function results(RadiologyOrder $order)
    {
        $this->authorize('view', $order);

        $results = $order->results()
            ->where('is_visible_to_patient', true)
            ->with('file')
            ->latest('id')
            ->get();

        return $this->success(RadiologyResultResource::collection($results), 'Radiology order results.');
    }
}
