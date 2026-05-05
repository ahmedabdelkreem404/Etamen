<?php

namespace App\Modules\CarePlans\Http\Requests;

use App\Modules\CarePlans\Domain\Enums\CarePlanMealType;
use App\Modules\CarePlans\Domain\Enums\MealLogStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MealLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_user_id' => ['prohibited'],
            'care_plan_meal_id' => ['nullable', 'integer', 'exists:care_plan_meals,id'],
            'logged_at' => ['required', 'date'],
            'meal_type' => ['nullable', Rule::in(CarePlanMealType::values())],
            'status' => ['required', Rule::in(MealLogStatus::values())],
            'description' => ['nullable', 'string', 'max:3000'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
