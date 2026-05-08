<?php

namespace App\Modules\Providers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProviderServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider_id' => ['prohibited'],
            'base_price' => ['prohibited'],
            'branch_id' => ['nullable', 'integer', 'exists:provider_branches,id'],
            'service_category_id' => ['nullable', 'integer', 'exists:service_categories,id'],
            'service_type' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:120'],
            'name_ar' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'online_available' => ['nullable', 'boolean'],
            'home_available' => ['nullable', 'boolean'],
            'branch_available' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
