<?php

namespace App\Modules\Medications\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MedicationReminderTimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'time_of_day' => ['required', 'date_format:H:i'],
            'label' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
