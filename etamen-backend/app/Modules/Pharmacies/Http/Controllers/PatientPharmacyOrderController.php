<?php

namespace App\Modules\Pharmacies\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Pharmacies\Application\Services\PharmacyOrderService;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderPaymentStatus;
use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderStatus;
use App\Modules\Pharmacies\Http\Requests\CreatePharmacyOrderRequest;
use App\Modules\Pharmacies\Http\Resources\PharmacyOrderResource;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyOrder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(PharmacyOrderStatus::values())],
            'payment_status' => ['nullable', Rule::in(PharmacyOrderPaymentStatus::values())],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'provider_id' => ['nullable', 'integer', 'exists:providers,id'],
            'search' => ['nullable', 'string', 'max:100'],
            'order_number' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1'],
        ]);

        $orders = PharmacyOrder::query()
            ->where('patient_user_id', $request->user()->id)
            ->with(['items', 'pharmacy', 'payment'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('order_status', $status))
            ->when($filters['payment_status'] ?? null, fn ($query, string $status) => $query->where('payment_status', $status))
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->when($filters['provider_id'] ?? null, fn ($query, int $providerId) => $query->where('pharmacy_provider_id', $providerId))
            ->when($filters['order_number'] ?? null, fn ($query, string $number) => $query->where('order_number', 'like', '%'.$number.'%'))
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('order_number', 'like', '%'.$search.'%')
                        ->orWhereHas('pharmacy', function ($query) use ($search): void {
                            $query->where('name_ar', 'like', '%'.$search.'%')
                                ->orWhere('name_en', 'like', '%'.$search.'%');
                        });
                });
            })
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
