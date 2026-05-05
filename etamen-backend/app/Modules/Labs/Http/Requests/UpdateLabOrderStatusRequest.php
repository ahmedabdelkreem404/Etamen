<?php

namespace App\Modules\Labs\Http\Requests;

use App\Modules\Labs\Domain\Enums\LabOrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLabOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(LabOrderStatus::values())],
            'reason' => [
                Rule::requiredIf(fn () => in_array($this->input('status'), [
                    LabOrderStatus::Rejected->value,
                    LabOrderStatus::Cancelled->value,
                ], true)),
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}
