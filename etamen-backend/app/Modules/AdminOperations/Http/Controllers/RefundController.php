<?php

namespace App\Modules\AdminOperations\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\AdminOperations\Http\Resources\RefundRequestResource;
use App\Modules\AdminOperations\Infrastructure\Models\RefundRequest;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Payments\Infrastructure\Models\Payment;
use Illuminate\Http\Request;

class RefundController extends ApiController
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function index(Request $request)
    {
        $refunds = RefundRequest::query()
            ->with('payment')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate($this->perPage($request, 30));

        return $this->success(RefundRequestResource::collection($refunds), 'Refund requests.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'payment_id' => ['nullable', 'integer', 'exists:payments,id'],
            'context_type' => ['nullable', 'string', 'max:120'],
            'context_id' => ['nullable', 'integer'],
            'amount' => ['nullable', 'numeric', 'min:1'],
            'currency' => ['nullable', 'string', 'size:3'],
            'reason' => ['required', 'string', 'max:3000'],
        ]);

        $payment = null;
        if (! empty($data['payment_id'])) {
            $payment = Payment::query()
                ->where('user_id', $request->user()->id)
                ->findOrFail($data['payment_id']);
        }

        $amount = $data['amount'] ?? $payment?->amount;
        abort_unless($amount !== null, 422, 'Refund amount is required when no payment is linked.');

        if ($payment && (float) $amount > (float) $payment->amount) {
            abort(422, 'Refund amount cannot exceed the linked payment amount.');
        }

        $refund = RefundRequest::query()->create([
            'user_id' => $request->user()->id,
            'payment_id' => $payment?->id,
            'context_type' => $data['context_type'] ?? ($payment ? class_basename((string) $payment->payable_type) : null),
            'context_id' => $data['context_id'] ?? $payment?->payable_id,
            'amount' => $amount,
            'currency' => $data['currency'] ?? $payment?->currency ?? 'EGP',
            'reason' => $data['reason'],
            'status' => RefundRequest::STATUS_REQUESTED,
        ]);

        $this->auditLogService->log('refund.requested', $refund, $request->user(), metadata: [
            'payment_id' => $refund->payment_id,
            'context_type' => $refund->context_type,
        ]);

        return $this->success(new RefundRequestResource($refund->load('payment')), 'Refund request created.', 201);
    }

    public function show(Request $request, RefundRequest $refund)
    {
        abort_unless((int) $refund->user_id === (int) $request->user()->id, 403, 'You cannot access this refund request.');

        return $this->success(new RefundRequestResource($refund->load('payment')), 'Refund request details.');
    }
}
