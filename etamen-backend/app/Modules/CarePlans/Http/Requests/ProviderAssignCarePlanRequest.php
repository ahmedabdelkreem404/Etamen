<?php

namespace App\Modules\CarePlans\Http\Requests;

use App\Modules\CarePlans\Domain\Enums\CarePlanType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProviderAssignCarePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_user_id' => ['required', 'integer', 'exists:users,id'],
            'assigned_by_user_id' => ['prohibited'],
            'provider_id' => ['prohibited'],
            'source' => ['prohibited'],
            'visibility' => ['prohibited'],
            'status' => ['prohibited'],
            'plan_type' => ['required', Rule::in(CarePlanType::values())],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'goal_text' => ['nullable', 'string', 'max:2000'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
