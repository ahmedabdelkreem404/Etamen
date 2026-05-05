<?php

namespace App\Modules\Notifications\Http\Requests;

use App\Modules\Notifications\Domain\Enums\NotificationDeviceType;
use App\Modules\Notifications\Domain\Enums\NotificationTokenProvider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NotificationTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'max:2048'],
            'provider' => ['required', Rule::in(NotificationTokenProvider::values())],
            'device_type' => ['required', Rule::in(NotificationDeviceType::values())],
            'device_name' => ['nullable', 'string', 'max:255'],
            'app_version' => ['nullable', 'string', 'max:100'],
            'locale' => ['nullable', Rule::in(['ar', 'en'])],
            'timezone' => ['nullable', 'timezone'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
