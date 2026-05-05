<?php

namespace App\Modules\Pharmacies\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadPharmacyPrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_user_id' => ['prohibited'],
            'uploaded_file_id' => ['prohibited'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'pharmacy_provider_id' => ['required', 'integer', 'exists:providers,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
