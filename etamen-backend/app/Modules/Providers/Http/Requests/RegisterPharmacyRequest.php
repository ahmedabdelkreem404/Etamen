<?php

namespace App\Modules\Providers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterPharmacyRequest extends FormRequest
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
            'license_number' => ['nullable', 'string', 'max:255'],
            'delivery_available' => ['nullable', 'boolean'],
            ...ProviderBranchRules::optional(),
            'approval_notes' => ['nullable', 'string'],
        ];
    }
}
