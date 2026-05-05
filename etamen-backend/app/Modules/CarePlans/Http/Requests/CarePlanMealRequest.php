<?php

namespace App\Modules\CarePlans\Http\Requests;

use App\Modules\CarePlans\Domain\Enums\CarePlanMealType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CarePlanMealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'care_plan_day_id' => ['required', 'integer', 'exists:care_plan_days,id'],
            'meal_type' => ['required', Rule::in(CarePlanMealType::values())],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'calories' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'protein_g' => ['nullable', 'numeric', 'min:0'],
            'carbs_g' => ['nullable', 'numeric', 'min:0'],
            'fat_g' => ['nullable', 'numeric', 'min:0'],
            'instructions' => ['nullable', 'string', 'max:3000'],
            'sort_order' => ['nullable', 'integer'],
            'is_required' => ['nullable', 'boolean'],
        ];
    }
}
