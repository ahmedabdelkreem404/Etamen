<?php

namespace App\Modules\Providers\Http\Requests;

use App\Modules\Providers\Domain\Enums\ProviderDocumentType;
use App\Modules\Providers\Domain\Enums\ProviderDocumentVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'document_type' => ['required', Rule::in(ProviderDocumentType::values())],
            'visibility' => ['nullable', Rule::in(ProviderDocumentVisibility::values())],
            'notes' => ['nullable', 'string'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ];
    }
}
