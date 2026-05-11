<?php

namespace App\Modules\Pharmacies\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Pharmacies\Application\Services\PharmacyOrderService;
use App\Modules\Pharmacies\Http\Requests\CreatePharmacyOrderRequest;
use App\Modules\Pharmacies\Http\Resources\PharmacyOrderResource;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyOrder;
use Illuminate\Http\Request;

class PatientPharmacyOrderController extends ApiController
{
    public function __construct(private readonly PharmacyOrderService $orderService) {}

    public function store(CreatePharmacyOrderRequest $request)
    {
        $order = $this->orderService->create($request->user(), $request->validated());

        return $this->success(new PharmacyOrderResource($order), 'Pharmacy order created.', 201);
    }

    public function index(Request $request)
    {
        $orders = PharmacyOrder::query()
            ->where('patient_user_id', $request->user()->id)
            ->with(['items', 'pharmacy', 'payment'])
            ->orderByDesc('id')
            ->limit($this->perPage($request))
            ->get();

        return $this->success(PharmacyOrderResource::collection($orders), 'Patient pharmacy orders.');
    }

    public function show(PharmacyOrder $order)
    {
        $this->authorize('view', $order);

        return $this->success(
            new PharmacyOrderResource($order->load(['items', 'pharmacy', 'payment.paymentMethod', 'prescription.uploadedFile', 'statusHistories'])),
            'Pharmacy order details.',
        );
    }

    public function pay(Request $request, PharmacyOrder $order)
    {
        $this->authorize('pay', $order);
        $order = $this->orderService->createPayment($request->user(), $order);

        return $this->success(new PharmacyOrderResource($order->load(['items', 'payment.paymentMethod'])), 'Pharmacy order payment created.');
    }

    public function cancel(Request $request, PharmacyOrder $order)
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $order = $this->orderService->patientCancel(
            $request->user(),
            $order,
            $validated['reason'] ?? null,
        );

        return $this->success(
            new PharmacyOrderResource($order),
            'Pharmacy order cancelled.',
        );
    }
}
