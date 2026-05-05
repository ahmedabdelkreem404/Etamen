<?php

namespace App\Modules\CarePlans\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CarePlanDayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'day_number' => ['nullable', 'integer', 'min:1'],
            'day_date' => ['nullable', 'date'],
            'title' => ['nullable', 'string', 'max:255'],
            'instructions' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $this->filled('day_number') && ! $this->filled('day_date')) {
                    $validator->errors()->add('day_number', 'Either day_number or day_date is required.');
                }
            },
        ];
    }
}
