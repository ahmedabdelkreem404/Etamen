<?php

namespace App\Modules\Labs\Http\Requests;

use App\Modules\Labs\Domain\Enums\LabOrderItemType;
use App\Modules\Labs\Domain\Enums\LabSampleCollectionMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateLabOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lab_provider_id' => ['required', 'integer', 'exists:providers,id'],
            'sample_collection_method' => ['required', Rule::in(LabSampleCollectionMethod::values())],
            'collection_address' => ['required_if:sample_collection_method,'.LabSampleCollectionMethod::HomeCollection->value, 'nullable', 'string', 'max:1000'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_type' => ['required', Rule::in(LabOrderItemType::values())],
            'items.*.test_id' => ['nullable', 'integer', 'exists:lab_tests,id', 'required_if:items.*.item_type,'.LabOrderItemType::Test->value],
            'items.*.package_id' => ['nullable', 'integer', 'exists:lab_packages,id', 'required_if:items.*.item_type,'.LabOrderItemType::Package->value],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'subtotal' => ['prohibited'],
            'discount_total' => ['prohibited'],
            'commission_amount' => ['prohibited'],
            'provider_net_amount' => ['prohibited'],
            'grand_total' => ['prohibited'],
            'currency' => ['prohibited'],
            'payment_status' => ['prohibited'],
            'order_status' => ['prohibited'],
            'paid_at' => ['prohibited'],
            'items.*.unit_price' => ['prohibited'],
            'items.*.line_total' => ['prohibited'],
        ];
    }
}
