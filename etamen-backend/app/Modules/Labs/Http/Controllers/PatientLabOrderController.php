<?php

namespace App\Modules\Labs\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Labs\Application\Services\LabOrderService;
use App\Modules\Labs\Domain\Enums\LabOrderPaymentStatus;
use App\Modules\Labs\Domain\Enums\LabOrderStatus;
use App\Modules\Labs\Domain\Enums\LabResultStatus;
use App\Modules\Labs\Domain\Enums\LabSampleCollectionMethod;
use App\Modules\Labs\Http\Requests\CreateLabOrderRequest;
use App\Modules\Labs\Http\Resources\LabOrderResource;
use App\Modules\Labs\Http\Resources\LabResultResource;
use App\Modules\Labs\Infrastructure\Models\LabOrder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(LabOrderStatus::values())],
            'payment_status' => ['nullable', Rule::in(LabOrderPaymentStatus::values())],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'provider_id' => ['nullable', 'integer', 'exists:providers,id'],
            'visit_type' => ['nullable', Rule::in([...LabSampleCollectionMethod::values(), 'home', 'branch'])],
            'home_or_branch' => ['nullable', Rule::in([...LabSampleCollectionMethod::values(), 'home', 'branch'])],
            'search' => ['nullable', 'string', 'max:100'],
            'order_number' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1'],
        ]);

        $visitType = $filters['visit_type'] ?? $filters['home_or_branch'] ?? null;
        $collectionMethod = match ($visitType) {
            'home' => LabSampleCollectionMethod::HomeCollection->value,
            'branch' => LabSampleCollectionMethod::BranchVisit->value,
            default => $visitType,
        };

        $orders = LabOrder::query()
            ->where('patient_user_id', $request->user()->id)
            ->with(['items', 'lab', 'payment'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('order_status', $status))
            ->when($filters['payment_status'] ?? null, fn ($query, string $status) => $query->where('payment_status', $status))
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->when($filters['provider_id'] ?? null, fn ($query, int $providerId) => $query->where('lab_provider_id', $providerId))
            ->when($collectionMethod, fn ($query, string $method) => $query->where('sample_collection_method', $method))
            ->when($filters['order_number'] ?? null, fn ($query, string $number) => $query->where('order_number', 'like', '%'.$number.'%'))
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('order_number', 'like', '%'.$search.'%')
                        ->orWhereHas('lab', function ($query) use ($search): void {
                            $query->where('name_ar', 'like', '%'.$search.'%')
                                ->orWhere('name_en', 'like', '%'.$search.'%');
                        });
                });
            })
            ->orderByDesc('id')
            ->limit($this->perPage($request))
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

    public function cancel(Request $request, LabOrder $order)
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
            new LabOrderResource($order),
            'Lab order cancelled.',
        );
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
