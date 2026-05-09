<?php

namespace App\Modules\Radiology\Http\Requests;

use App\Modules\Radiology\Domain\Enums\RadiologyResultType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadRadiologyResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp'],
            'result_type' => ['nullable', 'string', Rule::in(RadiologyResultType::values())],
            'title_ar' => ['nullable', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'notes_ar' => ['nullable', 'string', 'max:2000'],
            'notes_en' => ['nullable', 'string', 'max:2000'],
            'is_visible_to_patient' => ['nullable', 'boolean'],
        ];
    }
}
