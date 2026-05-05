<?php

namespace App\Modules\Payments\Infrastructure\Gateways;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymobGateway
{
    public function createSession(array $payload): array
    {
        $this->assertConfiguredForSession();

        $requestPayload = [
            'amount' => (int) round(((float) $payload['amount']) * 100),
            'currency' => $payload['currency'] ?? 'EGP',
            'payment_methods' => array_values(array_filter([
                config('paymob.card_integration_id'),
                config('paymob.wallet_integration_id'),
            ])),
            'billing_data' => [
                'first_name' => $payload['customer']['name'] ?? 'Etamen',
                'last_name' => 'Customer',
                'email' => $payload['customer']['email'] ?? 'customer@example.com',
                'phone_number' => $payload['customer']['phone'] ?? '01000000000',
            ],
            'extras' => [
                'payment_id' => $payload['payment_id'],
                'internal_reference' => $payload['merchant_order_id'],
            ],
            'special_reference' => $payload['merchant_order_id'],
            'notification_url' => config('paymob.webhook_url'),
            'redirection_url' => config('paymob.callback_url'),
        ];

        $response = Http::withToken((string) config('paymob.secret_key'))
            ->acceptJson()
            ->post((string) config('paymob.intention_url'), $requestPayload);

        if (! $response->successful()) {
            throw ValidationException::withMessages([
                'paymob' => ['Paymob session could not be created.'],
            ]);
        }

        $data = $response->json();
        $clientSecret = Arr::get($data, 'client_secret');

        return [
            'gateway_reference' => (string) (Arr::get($data, 'id') ?? Arr::get($data, 'intention_id') ?? $payload['merchant_order_id']),
            'client_secret' => $clientSecret,
            'checkout_url' => $this->checkoutUrl($clientSecret),
            'request_payload' => $requestPayload,
            'response_payload' => $data,
        ];
    }

    public function verifyCallback(array $payload, ?string $providedHmac = null): bool
    {
        return $this->verifyHmac($payload, $providedHmac);
    }

    public function verifyWebhook(array $payload, ?string $providedHmac = null): bool
    {
        return $this->verifyHmac($payload, $providedHmac);
    }

    public function getPaymentStatus(string $gatewayReference): array
    {
        throw ValidationException::withMessages([
            'paymob' => ['Paymob status polling is not enabled for this sprint. Use verified callbacks/webhooks.'],
        ]);
    }

    public function calculateHmac(array $payload): string
    {
        $secret = (string) config('paymob.hmac_secret');

        if ($secret === '') {
            throw ValidationException::withMessages([
                'paymob' => ['Paymob HMAC secret is not configured.'],
            ]);
        }

        $data = Arr::get($payload, 'obj', $payload);
        $concatenated = collect($this->hmacFields())
            ->map(fn (string $field): string => $this->stringValue(Arr::get($data, $field)))
            ->implode('');

        return hash_hmac('sha512', $concatenated, $secret);
    }

    private function verifyHmac(array $payload, ?string $providedHmac = null): bool
    {
        $providedHmac ??= Arr::get($payload, 'hmac');

        if (! is_string($providedHmac) || $providedHmac === '') {
            return false;
        }

        return hash_equals($this->calculateHmac($payload), Str::lower($providedHmac));
    }

    private function checkoutUrl(?string $clientSecret): ?string
    {
        if (! $clientSecret) {
            return null;
        }

        $baseUrl = rtrim((string) config('paymob.unified_checkout_url'), '/');
        $publicKey = config('paymob.public_key');

        return $baseUrl.'/?publicKey='.urlencode((string) $publicKey).'&clientSecret='.urlencode($clientSecret);
    }

    private function assertConfiguredForSession(): void
    {
        $missing = [];

        foreach (['secret_key', 'public_key', 'intention_url', 'unified_checkout_url', 'webhook_url', 'callback_url'] as $key) {
            if (! config('paymob.'.$key)) {
                $missing[] = 'PAYMOB_'.Str::upper($key);
            }
        }

        if (! config('paymob.card_integration_id') && ! config('paymob.wallet_integration_id')) {
            $missing[] = 'PAYMOB_CARD_INTEGRATION_ID or PAYMOB_WALLET_INTEGRATION_ID';
        }

        if ($missing !== []) {
            throw ValidationException::withMessages([
                'paymob' => ['Missing Paymob configuration: '.implode(', ', $missing)],
            ]);
        }
    }

    private function hmacFields(): array
    {
        return [
            'amount_cents',
            'created_at',
            'currency',
            'error_occured',
            'has_parent_transaction',
            'id',
            'integration_id',
            'is_3d_secure',
            'is_auth',
            'is_capture',
            'is_refunded',
            'is_standalone_payment',
            'is_voided',
            'order.id',
            'owner',
            'pending',
            'source_data.pan',
            'source_data.sub_type',
            'source_data.type',
            'success',
        ];
    }

    private function stringValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return '';
        }

        return (string) $value;
    }
}
