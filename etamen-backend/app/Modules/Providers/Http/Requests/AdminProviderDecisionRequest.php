<?php

namespace App\Modules\Providers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminProviderDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPlatformAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string'],
        ];
    }
}
