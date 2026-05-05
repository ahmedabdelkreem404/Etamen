<?php

namespace App\Modules\AI\Http\Requests;

use App\Modules\AI\Domain\Enums\AiLanguage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AiMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:4000'],
            'language' => ['nullable', Rule::in(AiLanguage::values())],
            'metadata' => ['nullable', 'array'],
            'patient_user_id' => ['prohibited'],
            'role' => ['prohibited'],
            'safety_classification' => ['prohibited'],
            'was_refused' => ['prohibited'],
            'provider' => ['prohibited'],
            'provider_message_id' => ['prohibited'],
            'token_count' => ['prohibited'],
        ];
    }
}
