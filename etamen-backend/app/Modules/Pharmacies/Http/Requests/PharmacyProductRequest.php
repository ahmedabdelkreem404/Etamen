<?php

namespace App\Modules\Pharmacies\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PharmacyProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'provider_id' => ['prohibited'],
            'image_file_id' => ['prohibited'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'name_en' => [$required, 'string', 'max:255'],
            'description_ar' => ['nullable', 'string', 'max:2000'],
            'description_en' => ['nullable', 'string', 'max:2000'],
            'sku' => ['nullable', 'string', 'max:100'],
            'price' => [$required, 'numeric', 'min:0.01', 'max:999999.99'],
            'requires_prescription' => ['sometimes', 'boolean'],
            'stock_quantity' => ['sometimes', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['sometimes', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
