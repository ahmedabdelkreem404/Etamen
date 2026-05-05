<?php

namespace App\Modules\Medications\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuickMedicationLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_user_id' => ['prohibited'],
            'scheduled_for' => ['nullable', 'date'],
            'taken_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
