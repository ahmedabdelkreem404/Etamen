<?php

namespace App\Modules\Payments\Infrastructure\Gateways;

use LogicException;

class PaymobGateway
{
    public function createSession(array $payload): array
    {
        throw new LogicException('Paymob createSession is a Sprint 3 integration point. No live API call is implemented in Sprint 0.');
    }

    public function verifyCallback(array $payload): bool
    {
        throw new LogicException('Paymob verifyCallback is a Sprint 3 integration point. No live API call is implemented in Sprint 0.');
    }

    public function verifyWebhook(array $payload): bool
    {
        throw new LogicException('Paymob verifyWebhook is a Sprint 3 integration point. No live API call is implemented in Sprint 0.');
    }

    public function getPaymentStatus(string $gatewayReference): array
    {
        throw new LogicException('Paymob getPaymentStatus is a Sprint 3 integration point. No live API call is implemented in Sprint 0.');
    }
}
