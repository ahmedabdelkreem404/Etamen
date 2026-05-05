<?php

namespace App\Modules\Health\Http\Requests;

use App\Modules\Health\Domain\Enums\VitalType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VitalTrendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vital_type' => ['required', Rule::in(VitalType::values())],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'group_by' => ['nullable', Rule::in(['day', 'week', 'month'])],
        ];
    }
}
