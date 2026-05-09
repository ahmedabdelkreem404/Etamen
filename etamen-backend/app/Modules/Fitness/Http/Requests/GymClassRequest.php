<?php

namespace App\Modules\Fitness\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GymClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['nullable', 'integer', 'exists:provider_branches,id'],
            'coach_provider_id' => ['nullable', 'integer', 'exists:providers,id'],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string', 'max:2000'],
            'description_en' => ['nullable', 'string', 'max:2000'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'is_active' => ['nullable', 'boolean'],
            'provider_id' => ['prohibited'],
        ];
    }
}
