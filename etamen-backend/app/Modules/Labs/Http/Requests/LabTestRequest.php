<?php

namespace App\Modules\Labs\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LabTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_ar' => ['nullable', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string', 'max:5000'],
            'description_en' => ['nullable', 'string', 'max:5000'],
            'code' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'sample_type' => ['nullable', 'string', 'max:255'],
            'preparation_instructions_ar' => ['nullable', 'string', 'max:5000'],
            'preparation_instructions_en' => ['nullable', 'string', 'max:5000'],
            'result_time_hours' => ['nullable', 'integer', 'min:1', 'max:8760'],
            'is_active' => ['sometimes', 'boolean'],
            'metadata' => ['nullable', 'array'],
            'provider_id' => ['prohibited'],
        ];
    }
}
