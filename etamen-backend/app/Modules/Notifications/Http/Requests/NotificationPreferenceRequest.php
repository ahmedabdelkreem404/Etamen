<?php

namespace App\Modules\Notifications\Http\Requests;

use App\Modules\Notifications\Domain\Enums\NotificationCategory;
use App\Modules\Notifications\Domain\Enums\NotificationChannel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NotificationPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'preferences' => ['required', 'array', 'min:1'],
            'preferences.*.channel' => ['required', Rule::in(NotificationChannel::values())],
            'preferences.*.category' => ['required', Rule::in(NotificationCategory::values())],
            'preferences.*.is_enabled' => ['required', 'boolean'],
            'preferences.*.quiet_hours_start' => ['nullable', 'date_format:H:i'],
            'preferences.*.quiet_hours_end' => ['nullable', 'date_format:H:i'],
            'preferences.*.timezone' => ['nullable', 'timezone'],
            'preferences.*.metadata' => ['nullable', 'array'],
        ];
    }
}
