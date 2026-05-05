<?php

namespace App\Modules\Health\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CurrentMedicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'medication_name' => ['required', 'string', 'max:255'],
            'dosage' => ['nullable', 'string', 'max:255'],
            'frequency_text' => ['nullable', 'string', 'max:255'],
            'started_at' => ['nullable', 'date', 'before_or_equal:today'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'prescribed_by' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
            'patient_user_id' => ['prohibited'],
            'reminder_times' => ['prohibited'],
            'reminder_enabled' => ['prohibited'],
        ];
    }
}
