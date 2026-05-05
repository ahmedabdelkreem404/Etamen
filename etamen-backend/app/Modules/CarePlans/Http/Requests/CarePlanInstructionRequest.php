<?php

namespace App\Modules\CarePlans\Http\Requests;

use App\Modules\CarePlans\Domain\Enums\CarePlanInstructionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CarePlanInstructionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'instruction_type' => ['required', Rule::in(CarePlanInstructionType::values())],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }
}
