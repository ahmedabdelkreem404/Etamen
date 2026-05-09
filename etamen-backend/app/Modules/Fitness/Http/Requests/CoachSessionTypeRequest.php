<?php

namespace App\Modules\Fitness\Http\Requests;

use App\Modules\Fitness\Domain\Enums\CoachSessionMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CoachSessionTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string', 'max:2000'],
            'description_en' => ['nullable', 'string', 'max:2000'],
            'duration_minutes' => ['required', 'integer', 'min:10', 'max:1440'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'session_mode' => ['required', Rule::in(CoachSessionMode::values())],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'provider_id' => ['prohibited'],
        ];
    }
}
