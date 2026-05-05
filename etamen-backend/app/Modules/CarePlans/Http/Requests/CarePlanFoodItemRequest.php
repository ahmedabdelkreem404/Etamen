<?php

namespace App\Modules\CarePlans\Http\Requests;

use App\Modules\CarePlans\Domain\Enums\CarePlanFoodCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CarePlanFoodItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', Rule::in(CarePlanFoodCategory::values())],
            'name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
