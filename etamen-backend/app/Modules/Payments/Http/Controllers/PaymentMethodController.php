<?php

namespace App\Modules\Payments\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Payments\Http\Resources\PaymentMethodResource;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;

class PaymentMethodController extends ApiController
{
    public function index()
    {
        $methods = PaymentMethod::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return $this->success(PaymentMethodResource::collection($methods), 'Active payment methods.');
    }
}
