<?php

namespace App\Modules\Payments\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Payments\Application\Services\ManualPaymentService;
use App\Modules\Payments\Http\Requests\RejectPaymentRequest;
use App\Modules\Payments\Http\Resources\InvoiceResource;
use App\Modules\Payments\Http\Resources\PaymentStatusResource;
use App\Modules\Payments\Infrastructure\Models\Invoice;
use App\Modules\Payments\Infrastructure\Models\Payment;
use Illuminate\Http\Request;

class AdminPaymentController extends ApiController
{
    public function __construct(private readonly ManualPaymentService $manualPaymentService) {}

    public function index(Request $request)
    {
        $payments = $this->paymentQuery($request)->get();

        return $this->success(PaymentStatusResource::collection($payments), 'Payments.');
    }

    public function show(Payment $payment)
    {
        return $this->success(
            new PaymentStatusResource($payment->load(['paymentMethod', 'payable', 'invoice'])),
            'Payment details.',
        );
    }

    public function pendingReview()
    {
        $payments = Payment::query()
            ->where('status', 'pending_review')
            ->with(['paymentMethod', 'payable', 'invoice'])
            ->orderByDesc('created_at')
            ->get();

        return $this->success(PaymentStatusResource::collection($payments), 'Pending-review payments.');
    }

    public function accept(Request $request, Payment $payment)
    {
        $payment = $this->manualPaymentService->accept($request->user(), $payment);

        return $this->success(new PaymentStatusResource($payment->load(['paymentMethod', 'payable', 'invoice'])), 'Payment accepted.');
    }

    public function reject(RejectPaymentRequest $request, Payment $payment)
    {
        $payment = $this->manualPaymentService->reject($request->user(), $payment, $request->validated('reason'));

        return $this->success(new PaymentStatusResource($payment->load(['paymentMethod', 'payable', 'invoice'])), 'Payment rejected.');
    }

    public function invoices()
    {
        $invoices = Invoice::query()
            ->with('payment.paymentMethod')
            ->orderByDesc('issued_at')
            ->get();

        return $this->success(InvoiceResource::collection($invoices), 'Invoices.');
    }

    private function paymentQuery(Request $request)
    {
        return Payment::query()
            ->with(['paymentMethod', 'payable', 'invoice'])
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('payment_method_id'), fn ($query, $methodId) => $query->where('payment_method_id', $methodId))
            ->when($request->query('date_from'), fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($request->query('date_to'), fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->orderByDesc('created_at');
    }
}
