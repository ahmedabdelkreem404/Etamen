<?php

namespace App\Modules\Medications\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MedicationRefillEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_user_id' => ['prohibited'],
            'event_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
