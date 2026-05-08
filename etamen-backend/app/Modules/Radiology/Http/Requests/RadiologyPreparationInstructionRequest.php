<?php

namespace App\Modules\Radiology\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RadiologyPreparationInstructionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPlatformAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'radiology_scan_category_id' => ['nullable', 'integer', 'exists:radiology_scan_categories,id', 'required_without:radiology_scan_id'],
            'radiology_scan_id' => ['nullable', 'integer', 'exists:radiology_scans,id', 'required_without:radiology_scan_category_id'],
            'title_ar' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'body_ar' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:5000'],
            'body_en' => ['nullable', 'string', 'max:5000'],
            'warning_ar' => ['nullable', 'string', 'max:5000'],
            'warning_en' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:-100000', 'max:100000'],
        ];
    }
}
