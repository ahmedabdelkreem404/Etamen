<?php

namespace App\Modules\AI\Http\Requests;

use App\Modules\AI\Domain\Enums\AiSafetyLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AiProviderConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider' => ['prohibited'],
            'is_active' => ['nullable', 'boolean'],
            'model' => ['nullable', 'string', 'max:255'],
            'safety_level' => ['nullable', Rule::in(AiSafetyLevel::values())],
            'encrypted_config' => ['nullable', 'array'],
        ];
    }
}
