<?php

namespace App\Modules\Payments\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Payments\Application\Services\PaymobPaymentService;
use App\Modules\Payments\Http\Resources\PaymentStatusResource;
use App\Modules\Payments\Infrastructure\Models\Payment;
use Illuminate\Http\Request;

class PaymobPaymentController extends ApiController
{
    public function __construct(private readonly PaymobPaymentService $paymobPaymentService) {}

    public function createSession(Request $request, Payment $payment)
    {
        $this->authorize('uploadProof', $payment);

        $result = $this->paymobPaymentService->createSession($request->user(), $payment);

        return $this->success([
            'payment' => new PaymentStatusResource($result['payment']),
            'checkout_url' => $result['checkout_url'],
            'client_secret' => $result['client_secret'],
            'gateway_reference' => $result['gateway_reference'],
        ], 'Paymob session created.');
    }

    public function callback(Request $request)
    {
        $payment = $this->paymobPaymentService->handleCallback(
            $request->all(),
            $this->hmacFromRequest($request),
            'callback',
        );

        return $this->success(new PaymentStatusResource($payment->load(['paymentMethod', 'payable', 'invoice'])), 'Paymob callback processed.');
    }

    public function webhook(Request $request)
    {
        $payment = $this->paymobPaymentService->handleWebhook(
            $request->all(),
            $this->hmacFromRequest($request),
        );

        return $this->success(new PaymentStatusResource($payment->load(['paymentMethod', 'payable', 'invoice'])), 'Paymob webhook processed.');
    }

    private function hmacFromRequest(Request $request): ?string
    {
        return $request->input('hmac')
            ?? $request->header('X-Paymob-Hmac')
            ?? $request->header('X-HMAC');
    }
}
