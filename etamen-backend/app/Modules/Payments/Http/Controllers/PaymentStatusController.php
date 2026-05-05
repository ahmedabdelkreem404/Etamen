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
        $this->authorize('view', $payment);

        return $this->success(new PaymentStatusResource($payment->load(['paymentMethod', 'payable', 'invoice'])), 'Payment status.');
    }
}
