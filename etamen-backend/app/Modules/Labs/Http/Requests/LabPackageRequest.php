<?php

namespace App\Modules\Labs\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LabPackageRequest extends FormRequest
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
            'price' => ['required', 'numeric', 'min:0.01'],
            'is_active' => ['sometimes', 'boolean'],
            'metadata' => ['nullable', 'array'],
            'test_ids' => ['required', 'array', 'min:1'],
            'test_ids.*' => ['required', 'integer', 'exists:lab_tests,id'],
            'provider_id' => ['prohibited'],
        ];
    }
}
