<?php

namespace App\Modules\Health\Http\Requests;

use App\Modules\Health\Domain\Enums\HealthGoalStatus;
use App\Modules\Health\Domain\Enums\HealthGoalType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HealthGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'goal_type' => ['required', Rule::in(HealthGoalType::values())],
            'title' => ['required', 'string', 'max:255'],
            'target_value' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:50'],
            'target_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'status' => ['sometimes', Rule::in(HealthGoalStatus::values())],
            'patient_user_id' => ['prohibited'],
        ];
    }
}
