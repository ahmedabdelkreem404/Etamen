<?php

namespace App\Modules\Payments\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Payments\Http\Resources\PaymentStatusResource;
use App\Modules\Payments\Infrastructure\Models\Payment;
use Illuminate\Http\Request;

class PaymentStatusController extends ApiController
{
    public function show(Request $request, Payment $payment)
    {
        $user = $request->user();

        if ($payment->user_id !== $user->id && ! $user->hasAnyRole(['super_admin', 'admin'])) {
            return $this->error('Forbidden.', [], 403);
        }

        return $this->success(new PaymentStatusResource($payment->load('paymentMethod')), 'Payment status.');
    }
}
