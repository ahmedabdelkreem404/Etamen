<?php

namespace App\Modules\Health\Http\Requests;

use App\Modules\Health\Domain\Enums\AllergySeverity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AllergyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'allergen' => ['required', 'string', 'max:255'],
            'reaction' => ['nullable', 'string', 'max:255'],
            'severity' => ['nullable', Rule::in(AllergySeverity::values())],
            'notes' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
            'patient_user_id' => ['prohibited'],
        ];
    }
}
