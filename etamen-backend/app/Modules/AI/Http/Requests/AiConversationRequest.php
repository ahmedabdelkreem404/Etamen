<?php

namespace App\Modules\AI\Http\Requests;

use App\Modules\AI\Domain\Enums\AiConversationStatus;
use App\Modules\AI\Domain\Enums\AiLanguage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AiConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_user_id' => ['prohibited'],
            'safety_level' => ['prohibited'],
            'title' => ['nullable', 'string', 'max:255'],
            'language' => ['nullable', Rule::in(AiLanguage::values())],
            'context_enabled' => ['nullable', 'boolean'],
            'provider' => ['nullable', Rule::in(['deepseek', 'gemini'])],
            'status' => [
                $this->isMethod('put') || $this->isMethod('patch') ? 'nullable' : 'prohibited',
                Rule::in([AiConversationStatus::Active->value, AiConversationStatus::Archived->value]),
            ],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
