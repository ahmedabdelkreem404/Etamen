<?php

namespace App\Modules\Health\Http\Requests;

use App\Modules\Health\Domain\Enums\BloodType;
use App\Modules\Health\Domain\Enums\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HealthProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(Gender::values())],
            'height_cm' => ['nullable', 'numeric', 'between:30,250'],
            'weight_kg' => ['nullable', 'numeric', 'between:1,400'],
            'blood_type' => ['nullable', Rule::in(BloodType::values())],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\\-\\s()]{7,30}$/'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'patient_user_id' => ['prohibited'],
        ];
    }
}
