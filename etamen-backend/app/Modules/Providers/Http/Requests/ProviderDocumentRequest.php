<?php

namespace App\Modules\Providers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProviderDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider_id' => ['prohibited'],
            'document_type' => ['required', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ];
    }
}
