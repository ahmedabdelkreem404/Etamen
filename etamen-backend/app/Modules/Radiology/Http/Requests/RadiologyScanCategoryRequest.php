<?php

namespace App\Modules\Radiology\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RadiologyScanCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPlatformAdmin() ?? false;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'code' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:120', Rule::unique('radiology_scan_categories', 'code')->ignore($categoryId)],
            'name_ar' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string', 'max:5000'],
            'description_en' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:-100000', 'max:100000'],
        ];
    }
}
