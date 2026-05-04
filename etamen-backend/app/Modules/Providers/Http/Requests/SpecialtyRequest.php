<?php

namespace App\Modules\Providers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SpecialtyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPlatformAdmin() ?? false;
    }

    public function rules(): array
    {
        $specialtyId = $this->route('specialty')?->id;

        return [
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('specialties', 'slug')->ignore($specialtyId)],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
