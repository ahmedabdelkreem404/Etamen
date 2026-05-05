<?php

namespace App\Modules\Payments\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Payments\Application\Services\ManualPaymentService;
use App\Modules\Payments\Http\Requests\SelectManualPaymentRequest;
use App\Modules\Payments\Http\Requests\UploadPaymentProofRequest;
use App\Modules\Payments\Http\Resources\PaymentStatusResource;
use App\Modules\Payments\Infrastructure\Models\Payment;

class ManualPaymentController extends ApiController
{
    public function __construct(private readonly ManualPaymentService $manualPaymentService) {}

    public function select(SelectManualPaymentRequest $request, Payment $payment)
    {
        $this->authorize('uploadProof', $payment);

        $result = $this->manualPaymentService->selectMethod(
            $request->user(),
            $payment,
            $request->validated('payment_method_id'),
        );

        return $this->success([
            'payment' => new PaymentStatusResource($result['payment']),
            'instructions_ar' => $result['instructions_ar'],
            'instructions_en' => $result['instructions_en'],
        ], 'Manual payment method selected.');
    }

    public function uploadProof(UploadPaymentProofRequest $request, Payment $payment)
    {
        $this->authorize('uploadProof', $payment);

        $payment = $this->manualPaymentService->uploadProof(
            $request->user(),
            $payment,
            $request->file('file'),
            $request->validated(),
        );

        return $this->success(new PaymentStatusResource($payment->load(['paymentMethod', 'payable', 'invoice'])), 'Payment proof uploaded.', 201);
    }
}
