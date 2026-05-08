<?php

namespace App\Modules\Radiology\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProviderRadiologyScanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider_id' => ['prohibited'],
            'created_by' => ['prohibited'],
            'updated_by' => ['prohibited'],
            'branch_id' => ['nullable', 'integer', 'exists:provider_branches,id'],
            'radiology_scan_category_id' => [$this->isMethod('post') ? 'required' : 'sometimes', 'integer', 'exists:radiology_scan_categories,id'],
            'name_ar' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string', 'max:5000'],
            'description_en' => ['nullable', 'string', 'max:5000'],
            'preparation_ar' => ['nullable', 'string', 'max:5000'],
            'preparation_en' => ['nullable', 'string', 'max:5000'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'requires_preparation' => ['sometimes', 'boolean'],
            'requires_fasting' => ['sometimes', 'boolean'],
            'contrast_required' => ['sometimes', 'boolean'],
            'home_available' => ['sometimes', 'boolean'],
            'branch_available' => ['sometimes', 'boolean'],
            'report_delivery_enabled' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
