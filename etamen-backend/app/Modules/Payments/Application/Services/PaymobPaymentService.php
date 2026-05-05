<?php

namespace App\Modules\Payments\Application\Services;

use App\Models\User;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\Payments\Domain\Enums\PaymentMethodType;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Infrastructure\Gateways\PaymobGateway;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Payments\Infrastructure\Models\PaymentAttempt;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymobPaymentService
{
    public function __construct(
        private readonly PaymobGateway $paymobGateway,
        private readonly PaymentVerificationService $paymentVerificationService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function createSession(User $patient, Payment $payment): array
    {
        return DB::transaction(function () use ($patient, $payment): array {
            $payment = Payment::query()->whereKey($payment->id)->with(['paymentMethod', 'user'])->lockForUpdate()->firstOrFail();

            if ((int) $payment->user_id !== (int) $patient->id) {
                throw ValidationException::withMessages([
                    'payment' => ['You cannot pay this payment.'],
                ]);
            }

            if ($payment->status === PaymentStatus::Verified) {
                throw ValidationException::withMessages([
                    'payment' => ['This payment is already verified.'],
                ]);
            }

            $method = PaymentMethod::query()
                ->where('type', PaymentMethodType::Paymob)
                ->where('is_active', true)
                ->first();

            if (! $method) {
                throw ValidationException::withMessages([
                    'payment_method' => ['Paymob is not currently available.'],
                ]);
            }

            $merchantOrderId = $this->merchantOrderId($payment);
            $requestPayload = [
                'payment_id' => $payment->id,
                'merchant_order_id' => $merchantOrderId,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'customer' => [
                    'name' => $payment->user->name,
                    'email' => $payment->user->email,
                ],
            ];

            $gatewayResponse = $this->paymobGateway->createSession($requestPayload);

            $attempt = $payment->attempts()->create([
                'method_type' => PaymentMethodType::Paymob,
                'gateway_reference' => $gatewayResponse['gateway_reference'] ?? $merchantOrderId,
                'request_payload' => $this->safeRequestPayload($requestPayload),
                'response_payload' => $this->safeGatewayResponse($gatewayResponse),
                'status' => 'created',
            ]);

            $this->transitionPayment($payment, PaymentStatus::PendingGateway, $patient, 'Paymob session created.', [
                'attempt_id' => $attempt->id,
                'gateway_reference' => $attempt->gateway_reference,
            ], ['payment_method_id' => $method->id]);

            $this->auditLogService->log('payment.paymob_session_created', $payment, $patient, metadata: [
                'attempt_id' => $attempt->id,
            ]);

            return [
                'payment' => $payment->refresh()->load('paymentMethod'),
                'checkout_url' => $gatewayResponse['checkout_url'] ?? null,
                'client_secret' => $gatewayResponse['client_secret'] ?? null,
                'gateway_reference' => $attempt->gateway_reference,
            ];
        });
    }

    public function handleCallback(array $payload, ?string $hmac, string $source): Payment
    {
        if (! $this->paymobGateway->verifyCallback($payload, $hmac)) {
            throw ValidationException::withMessages([
                'hmac' => ['Invalid Paymob HMAC.'],
            ]);
        }

        return $this->handleVerifiedPayload($payload, $source);
    }

    public function handleWebhook(array $payload, ?string $hmac): Payment
    {
        if (! $this->paymobGateway->verifyWebhook($payload, $hmac)) {
            throw ValidationException::withMessages([
                'hmac' => ['Invalid Paymob HMAC.'],
            ]);
        }

        return $this->handleVerifiedPayload($payload, 'webhook');
    }

    private function handleVerifiedPayload(array $payload, string $source): Payment
    {
        return DB::transaction(function () use ($payload, $source): Payment {
            $payment = $this->paymentFromPaymobPayload($payload);
            $data = Arr::get($payload, 'obj', $payload);
            $success = filter_var(Arr::get($data, 'success', false), FILTER_VALIDATE_BOOLEAN);
            $pending = filter_var(Arr::get($data, 'pending', false), FILTER_VALIDATE_BOOLEAN);
            $gatewayReference = (string) (Arr::get($data, 'id') ?? Arr::get($payload, 'id') ?? '');

            if ($gatewayReference !== '') {
                PaymentAttempt::query()
                    ->where('payment_id', $payment->id)
                    ->latest('id')
                    ->first()
                    ?->update([
                        'gateway_reference' => $gatewayReference,
                        'response_payload' => $this->safeGatewayResponse($payload),
                        'status' => $success && ! $pending ? 'paid' : 'callback_received',
                    ]);
            }

            if ($success && ! $pending) {
                return $this->paymentVerificationService->verify($payment, null, 'Paymob verified callback.', [
                    'source' => $source,
                    'gateway_reference' => $gatewayReference,
                ]);
            }

            return $this->paymentVerificationService->fail($payment, null, 'Paymob failed callback.', [
                'source' => $source,
                'gateway_reference' => $gatewayReference,
            ]);
        });
    }

    private function paymentFromPaymobPayload(array $payload): Payment
    {
        $data = Arr::get($payload, 'obj', $payload);
        $merchantOrderId = Arr::get($data, 'order.merchant_order_id')
            ?? Arr::get($data, 'merchant_order_id')
            ?? Arr::get($payload, 'merchant_order_id');

        if (is_string($merchantOrderId) && preg_match('/^ETAMEN-PAY-(\d+)$/', $merchantOrderId, $matches)) {
            return Payment::query()->whereKey((int) $matches[1])->lockForUpdate()->firstOrFail();
        }

        $gatewayReference = Arr::get($data, 'id') ?? Arr::get($payload, 'transaction_id');

        if ($gatewayReference) {
            $attempt = PaymentAttempt::query()
                ->where('gateway_reference', (string) $gatewayReference)
                ->with('payment')
                ->latest('id')
                ->first();

            if ($attempt?->payment) {
                return Payment::query()->whereKey($attempt->payment_id)->lockForUpdate()->firstOrFail();
            }
        }

        throw ValidationException::withMessages([
            'payment' => ['Could not match Paymob callback to an internal payment.'],
        ]);
    }

    private function transitionPayment(Payment $payment, PaymentStatus $to, User $actor, string $reason, array $metadata = [], array $extra = []): void
    {
        $from = $payment->status;

        $payment->forceFill([
            'status' => $to,
            ...$extra,
        ])->save();

        if ($from !== $to) {
            $payment->statusHistories()->create([
                'from_status' => $from->value,
                'to_status' => $to->value,
                'actor_id' => $actor->id,
                'reason' => $reason,
                'metadata' => $metadata,
            ]);
        }
    }

    private function merchantOrderId(Payment $payment): string
    {
        return 'ETAMEN-PAY-'.$payment->id;
    }

    private function safeRequestPayload(array $payload): array
    {
        return Arr::except($payload, ['customer']);
    }

    private function safeGatewayResponse(array $response): array
    {
        return Arr::except($response, ['client_secret', 'secret_key', 'token']);
    }
}
