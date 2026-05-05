<?php

namespace App\Modules\Pharmacies\Http\Requests;

use App\Modules\Pharmacies\Domain\Enums\PharmacyDeliveryMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePharmacyOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_user_id' => ['prohibited'],
            'payment_id' => ['prohibited'],
            'subtotal' => ['prohibited'],
            'grand_total' => ['prohibited'],
            'commission_amount' => ['prohibited'],
            'provider_net_amount' => ['prohibited'],
            'payment_status' => ['prohibited'],
            'order_status' => ['prohibited'],
            'pharmacy_provider_id' => ['required', 'integer', 'exists:providers,id'],
            'prescription_id' => ['nullable', 'integer', 'exists:pharmacy_prescriptions,id'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => ['required', 'integer', 'exists:pharmacy_products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'items.*.stock_quantity' => ['prohibited'],
            'items.*.unit_price' => ['prohibited'],
            'items.*.line_total' => ['prohibited'],
            'delivery_method' => ['required', Rule::in(PharmacyDeliveryMethod::values())],
            'delivery_address' => ['nullable', 'string', 'max:1000', 'required_if:delivery_method,delivery'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
