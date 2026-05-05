<?php

namespace App\Modules\AI\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AiToggleContextRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'enabled' => ['required', 'boolean'],
        ];
    }
}
