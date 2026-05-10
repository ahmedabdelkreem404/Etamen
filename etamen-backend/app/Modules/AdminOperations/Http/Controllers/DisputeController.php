<?php

namespace App\Modules\AdminOperations\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\AdminOperations\Http\Resources\DisputeResource;
use App\Modules\AdminOperations\Infrastructure\Models\Dispute;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Payments\Infrastructure\Models\Payment;
use Illuminate\Http\Request;

class DisputeController extends ApiController
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function index(Request $request)
    {
        $disputes = Dispute::query()
            ->with(['provider', 'payment'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate($this->perPage($request, 30));

        return $this->success(DisputeResource::collection($disputes), 'Disputes.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'provider_id' => ['nullable', 'integer', 'exists:providers,id'],
            'payment_id' => ['nullable', 'integer', 'exists:payments,id'],
            'context_type' => ['nullable', 'string', 'max:120'],
            'context_id' => ['nullable', 'integer'],
            'reason' => ['required', 'string', 'max:3000'],
            'priority' => ['nullable', 'in:low,normal,high,urgent'],
        ]);

        $payment = null;
        if (! empty($data['payment_id'])) {
            $payment = Payment::query()
                ->where('user_id', $request->user()->id)
                ->findOrFail($data['payment_id']);
        }

        $dispute = Dispute::query()->create([
            'user_id' => $request->user()->id,
            'provider_id' => $data['provider_id'] ?? $payment?->provider_id,
            'payment_id' => $payment?->id,
            'context_type' => $data['context_type'] ?? ($payment ? class_basename((string) $payment->payable_type) : null),
            'context_id' => $data['context_id'] ?? $payment?->payable_id,
            'reason' => $data['reason'],
            'status' => Dispute::STATUS_OPEN,
            'priority' => $data['priority'] ?? 'normal',
        ]);

        $this->auditLogService->log('dispute.opened', $dispute, $request->user(), metadata: [
            'provider_id' => $dispute->provider_id,
            'payment_id' => $dispute->payment_id,
        ]);

        return $this->success(new DisputeResource($dispute->load(['provider', 'payment'])), 'Dispute created.', 201);
    }

    public function show(Request $request, Dispute $dispute)
    {
        abort_unless((int) $dispute->user_id === (int) $request->user()->id, 403, 'You cannot access this dispute.');

        return $this->success(new DisputeResource($dispute->load(['provider', 'payment'])), 'Dispute details.');
    }
}
