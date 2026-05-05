<?php

namespace App\Modules\Pharmacies\Http\Requests;

use App\Modules\Pharmacies\Domain\Enums\PharmacyOrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePharmacyOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(PharmacyOrderStatus::values())],
            'reason' => ['nullable', 'string', 'max:1000', 'required_if:status,rejected,cancelled'],
        ];
    }
}
