<?php

namespace App\Modules\Health\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SurgeryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'surgery_name' => ['required', 'string', 'max:255'],
            'surgery_date' => ['nullable', 'date', 'before_or_equal:today'],
            'hospital_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'patient_user_id' => ['prohibited'],
        ];
    }
}
