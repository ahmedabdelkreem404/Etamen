<?php

namespace App\Modules\Providers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'provider_name_ar' => ['nullable', 'string', 'max:255'],
            'provider_name_en' => ['required', 'string', 'max:255'],
            'provider_email' => ['nullable', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'title' => ['nullable', 'string', 'max:255'],
            'bio_ar' => ['nullable', 'string'],
            'bio_en' => ['nullable', 'string'],
            'consultation_fee' => ['nullable', 'numeric', 'min:0'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:80'],
            'specialty_ids' => ['nullable', 'array'],
            'specialty_ids.*' => ['integer', 'exists:specialties,id'],
            ...ProviderBranchRules::optional(),
            'approval_notes' => ['nullable', 'string'],
        ];
    }
}
